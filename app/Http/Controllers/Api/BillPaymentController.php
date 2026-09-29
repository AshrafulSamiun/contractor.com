<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\BillPayment;
use App\Models\PurchaseInvoice;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BillPaymentController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = BillPayment::with(['creator:id,name', 'seller:id,seller_name,contact_person,phone,email,address,tax_number', 'allocations.purchaseInvoice:id,invoice_no,po_no,invoice_date,due_date,total,paid,payment_status'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('payment_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('payment_no', 'like', "%{$term}%")->orWhere('reference_no', 'like', "%{$term}%")
                ->orWhereHas('seller', fn ($seller) => $seller->where('seller_name', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('seller_id')) $query->where('seller_id', $request->seller_id);
        if ($request->filled('from')) $query->whereDate('payment_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('payment_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, BillPayment $billPayment)
    {
        $this->ensureProject($request, $billPayment);
        return response()->json(['data' => $this->present($billPayment)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $payment = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $allocations = $data['allocations']; unset($data['allocations']);
            $data['payment_no'] = $data['payment_no'] ?: $this->nextNumber($projectId);
            abort_if(BillPayment::where('project_id', $projectId)->where('payment_no', $data['payment_no'])->exists(), 422, 'This bill payment number is already in use.');
            $payment = BillPayment::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft']);
            $this->syncAllocations($payment, $allocations);
            $this->audit($request, $payment, 'Created');
            return $payment;
        });
        return response()->json(['data' => $this->present($payment)], 201);
    }

    public function update(Request $request, BillPayment $billPayment)
    {
        $this->ensureProject($request, $billPayment);
        abort_unless($billPayment->status === 'Draft', 422, 'Only draft bill payments can be edited.');
        $data = $this->validated($request, $billPayment->project_id, $billPayment);
        DB::transaction(function () use ($request, $billPayment, $data) {
            $payment = BillPayment::whereKey($billPayment->id)->lockForUpdate()->firstOrFail();
            $allocations = $data['allocations']; unset($data['allocations']);
            $payment->fill($data); $changes = array_keys($payment->getDirty()); $payment->save();
            $this->syncAllocations($payment, $allocations); $this->audit($request, $payment, 'Updated', $changes);
        });
        return response()->json(['data' => $this->present($billPayment->fresh())]);
    }

    public function destroy(Request $request, BillPayment $billPayment)
    {
        $this->ensureProject($request, $billPayment);
        abort_unless($billPayment->status === 'Draft', 422, 'Only draft bill payments can be deleted.');
        $paths = DB::table('bill_payment_attachments')->where('bill_payment_id', $billPayment->id)->pluck('path')->all();
        $billPayment->delete(); Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function post(Request $request, BillPayment $billPayment)
    {
        $this->ensureProject($request, $billPayment);
        DB::transaction(function () use ($request, $billPayment) {
            $payment = BillPayment::whereKey($billPayment->id)->lockForUpdate()->firstOrFail();
            abort_unless($payment->status === 'Draft', 422, 'This bill payment has already been posted.');
            $allocations = $payment->allocations()->orderBy('id')->get();
            abort_if($allocations->isEmpty(), 422, 'Allocate this payment to at least one invoice.');
            $writeOff = (float) $payment->write_off;
            foreach ($allocations as $index => $allocation) {
                $invoice = PurchaseInvoice::whereKey($allocation->purchase_invoice_id)->lockForUpdate()->firstOrFail();
                abort_unless((int) $invoice->project_id === (int) $payment->project_id && (int) $invoice->seller_id === (int) $payment->seller_id, 422, 'An allocation no longer belongs to this vendor.');
                abort_unless($invoice->status === 'Approved' && $invoice->currency_code === $payment->currency_code, 422, 'An allocated invoice is not eligible for payment.');
                $extra = $index === $allocations->count() - 1 ? $writeOff : 0;
                $due = round((float) $invoice->total - (float) $invoice->paid, 2);
                abort_if((float) $allocation->amount + $extra > $due, 422, 'An allocation exceeds the current invoice balance.');
                if ((float) $allocation->amount > 0) {
                    $invoice->payments()->create([
                        'payment_no' => $payment->payment_no, 'payment_date' => $payment->payment_date,
                        'amount' => $allocation->amount, 'method' => $payment->payment_method,
                        'reference' => $payment->reference_no, 'notes' => $payment->notes,
                    ]);
                }
                $invoice->paid = round((float) $invoice->paid + (float) $allocation->amount + $extra, 2);
                $invoice->payment_status = (float) $invoice->paid >= (float) $invoice->total ? 'Paid' : 'Partial';
                $invoice->save();
            }
            $payment->status = 'Posted'; $payment->posted_at = now(); $payment->save();
            $this->audit($request, $payment, 'Posted payment', ['amount' => $payment->amount, 'write_off' => $payment->write_off]);
        });
        return response()->json(['data' => $this->present($billPayment->fresh())]);
    }

    public function attach(Request $request, BillPayment $billPayment)
    {
        $this->ensureProject($request, $billPayment);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file'); $path = $file->store('bill-payments/'.$billPayment->id, 'local');
        try {
            DB::table('bill_payment_attachments')->insert(['bill_payment_id' => $billPayment->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $billPayment, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($billPayment->fresh())]);
    }

    public function download(Request $request, BillPayment $billPayment, int $attachment)
    {
        $this->ensureProject($request, $billPayment);
        $file = DB::table('bill_payment_attachments')->where('bill_payment_id', $billPayment->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?BillPayment $payment = null): array
    {
        $data = $request->validate([
            'payment_no' => [$payment ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('bill_payments')->where('project_id', $projectId)->ignore($payment?->id)],
            'seller_id' => ['required', 'integer', Rule::exists('sellers', 'id')->where('project_id', $projectId)],
            'payment_date' => 'required|date', 'payment_method' => 'required|string|max:255', 'bank_account' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255', 'currency_code' => 'required|string|size:3',
            'amount' => 'required|numeric|gt:0|max:999999999999', 'write_off' => 'nullable|numeric|min:0|max:999999999999',
            'write_off_account' => 'nullable|string|max:255', 'take_discount' => 'boolean', 'group_by_invoice' => 'boolean',
            'discount_date' => 'nullable|date', 'memo' => 'nullable|string|max:5000', 'notes' => 'nullable|string|max:10000',
            'allocations' => 'required|array|min:1|max:500', 'allocations.*.purchase_invoice_id' => 'required|integer|distinct',
            'allocations.*.amount' => 'required|numeric|min:0|max:999999999999',
        ]);
        $data['payment_no'] ??= ''; $data['write_off'] = round((float) ($data['write_off'] ?? 0), 2);
        $data['amount'] = round((float) $data['amount'], 2);
        $allocated = round(collect($data['allocations'])->sum('amount'), 2);
        if ($allocated !== $data['amount']) throw ValidationException::withMessages(['allocations' => 'Allocated invoice amounts must equal the payment amount.']);
        if ($data['write_off'] > 0 && empty($data['write_off_account'])) throw ValidationException::withMessages(['write_off_account' => 'Select a write-off account.']);
        foreach ($data['allocations'] as $allocation) {
            $invoice = PurchaseInvoice::where('project_id', $projectId)->findOrFail($allocation['purchase_invoice_id']);
            if ((int) $invoice->seller_id !== (int) $data['seller_id'] || $invoice->currency_code !== $data['currency_code'] || $invoice->status !== 'Approved') {
                throw ValidationException::withMessages(['allocations' => 'All allocated invoices must be approved invoices for the selected vendor and currency.']);
            }
            if ((float) $allocation['amount'] > round((float) $invoice->total - (float) $invoice->paid, 2)) throw ValidationException::withMessages(['allocations' => 'An allocation exceeds its invoice balance.']);
        }
        return $data;
    }

    private function syncAllocations(BillPayment $payment, array $allocations): void
    {
        $payment->allocations()->delete();
        foreach ($allocations as $allocation) $payment->allocations()->create($allocation);
    }

    private function present(BillPayment $payment): array
    {
        $payment->load(['creator:id,name', 'seller', 'allocations.purchaseInvoice.purchaseOrder:id,po_no,po_date']);
        $invoiceIds = $payment->allocations->pluck('purchase_invoice_id');
        return $payment->toArray() + [
            'related_documents' => [
                'returns' => DB::table('purchase_returns')->whereIn('purchase_invoice_id', $invoiceIds)->where('status', 'Returned')->get(),
                'debit_notes' => DB::table('seller_debit_notes')->whereIn('purchase_invoice_id', $invoiceIds)->whereIn('status', ['Approved', 'Closed'])->get(),
                'credit_notes' => DB::table('seller_credit_notes')->whereIn('purchase_invoice_id', $invoiceIds)->where('status', 'Approved')->get(),
            ],
            'activities' => DB::table('bill_payment_activities')->where('bill_payment_id', $payment->id)->latest('id')->get(),
            'attachments' => DB::table('bill_payment_attachments')->where('bill_payment_id', $payment->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, BillPayment $payment, string $action, array $changes = []): void
    {
        DB::table('bill_payment_activities')->insert(['bill_payment_id' => $payment->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function ensureProject(Request $request, BillPayment $payment): void
    {
        abort_unless((int) $payment->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) BillPayment::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'BP-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (BillPayment::where('project_id', $projectId)->where('payment_no', $number)->exists());
        return $number;
    }
}
