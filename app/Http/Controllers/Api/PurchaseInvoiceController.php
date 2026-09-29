<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PurchaseInvoiceController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['creator:id,name', 'payments', 'purchaseOrder:id,po_no,po_date'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('invoice_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('invoice_no', 'like', "%{$term}%")
                ->orWhere('po_no', 'like', "%{$term}%")->orWhere('seller_name', 'like', "%{$term}%")
                ->orWhere('seller_no', 'like', "%{$term}%"));
        }
        foreach (['status', 'payment_status', 'department', 'project', 'invoice_type'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->filled('seller')) $query->where('seller_name', $request->seller);
        if ($request->filled('from')) $query->whereDate('invoice_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('invoice_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        return response()->json(['data' => $this->present($purchaseInvoice)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $invoice = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['invoice_no'] = $data['invoice_no'] ?: $this->nextNumber($projectId);
            abort_if(PurchaseInvoice::where('project_id', $projectId)->where('invoice_no', $data['invoice_no'])->exists(), 422, 'This invoice number is already in use.');
            $invoice = PurchaseInvoice::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft', 'payment_status' => 'Unpaid', 'paid' => 0]);
            $this->audit($request, $invoice, 'Created');
            return $invoice;
        });
        return response()->json(['data' => $this->present($invoice)], 201);
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        abort_if((float) $purchaseInvoice->paid > 0, 422, 'An invoice with payments cannot be edited.');
        $data = $this->validated($request, $purchaseInvoice->project_id, $purchaseInvoice);
        DB::transaction(function () use ($request, $purchaseInvoice, $data) {
            $invoice = PurchaseInvoice::whereKey($purchaseInvoice->id)->lockForUpdate()->firstOrFail();
            $invoice->fill($data);
            $changes = array_keys($invoice->getDirty());
            if ($changes) {
                $invoice->status = 'Draft';
                $invoice->save();
                $this->audit($request, $invoice, 'Updated', $changes);
            }
        });
        return response()->json(['data' => $this->present($purchaseInvoice->fresh())]);
    }

    public function destroy(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        abort_if((float) $purchaseInvoice->paid > 0, 422, 'An invoice with payments cannot be deleted.');
        $paths = DB::table('purchase_invoice_attachments')->where('purchase_invoice_id', $purchaseInvoice->id)->pluck('path')->all();
        $purchaseInvoice->delete();
        Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        DB::transaction(function () use ($request, $purchaseInvoice, $action) {
            $invoice = PurchaseInvoice::whereKey($purchaseInvoice->id)->lockForUpdate()->firstOrFail();
            $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
            abort_unless(in_array($invoice->status, $allowed), 422, 'This approval action is no longer available.');
            $invoice->status = ['submit' => 'Pending', 'approve' => 'Approved', 'reject' => 'Rejected'][$action];
            $invoice->save();
            $this->audit($request, $invoice, $invoice->status);
        });
        return response()->json(['data' => $this->present($purchaseInvoice->fresh())]);
    }

    public function pay(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        $data = $request->validate([
            'payment_no' => ['required', 'string', 'max:100', Rule::unique('purchase_invoice_payments')->where('purchase_invoice_id', $purchaseInvoice->id)],
            'payment_date' => 'required|date', 'amount' => 'required|numeric|gt:0|max:999999999999',
            'method' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000',
        ]);
        DB::transaction(function () use ($request, $purchaseInvoice, $data) {
            $invoice = PurchaseInvoice::whereKey($purchaseInvoice->id)->lockForUpdate()->firstOrFail();
            abort_unless($invoice->status === 'Approved', 422, 'Approve the invoice before receiving payment.');
            $due = round((float) $invoice->total - (float) $invoice->paid, 2);
            abort_if(round((float) $data['amount'], 2) > $due, 422, 'Payment amount exceeds the outstanding balance.');
            $invoice->payments()->create($data);
            $invoice->paid = round((float) $invoice->paid + (float) $data['amount'], 2);
            $invoice->payment_status = (float) $invoice->paid >= (float) $invoice->total ? 'Paid' : 'Partial';
            $invoice->save();
            $this->audit($request, $invoice, 'Received payment', ['number' => $data['payment_no'], 'amount' => $data['amount']]);
        });
        return response()->json(['data' => $this->present($purchaseInvoice->fresh())]);
    }

    public function attach(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->ensureProject($request, $purchaseInvoice);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file');
        $path = $file->store('purchase-invoices/'.$purchaseInvoice->id, 'local');
        try {
            DB::table('purchase_invoice_attachments')->insert([
                'purchase_invoice_id' => $purchaseInvoice->id, 'name' => basename($file->getClientOriginalName()),
                'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit($request, $purchaseInvoice, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($purchaseInvoice->fresh())]);
    }

    public function download(Request $request, PurchaseInvoice $purchaseInvoice, int $attachment)
    {
        $this->ensureProject($request, $purchaseInvoice);
        $file = DB::table('purchase_invoice_attachments')->where('purchase_invoice_id', $purchaseInvoice->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?PurchaseInvoice $invoice = null): array
    {
        $data = $request->validate([
            'invoice_no' => [$invoice ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('purchase_invoices')->where('project_id', $projectId)->ignore($invoice?->id)],
            'purchase_order_id' => ['nullable', 'integer', Rule::exists('purchase_orders', 'id')->where('project_id', $projectId)],
            'seller_id' => ['nullable', 'integer', Rule::exists('sellers', 'id')->where('project_id', $projectId)],
            'po_no' => 'nullable|string|max:40', 'seller_no' => 'nullable|string|max:40', 'seller_name' => 'required|string|max:255',
            'seller_details' => 'nullable|array', 'requester_details' => 'nullable|array', 'delivery_details' => 'nullable|array',
            'requested_by' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255', 'project' => 'nullable|string|max:255',
            'invoice_date' => 'required|date', 'due_date' => 'nullable|date|after_or_equal:invoice_date', 'delivery_date' => 'nullable|date',
            'delivery_location' => 'nullable|string|max:255', 'currency_code' => 'required|string|size:3',
            'invoice_type' => 'required|in:Regular,Credit Note', 'payment_term' => 'nullable|string|max:100', 'payment_method' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:500', 'items.*.name' => 'required|string|max:255', 'items.*.code' => 'nullable|string|max:100',
            'items.*.uom' => 'nullable|string|max:40', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000',
            'items.*.cost_rate' => 'required|numeric|min:0|max:1000000', 'items.*.sales_tax' => 'required|numeric|min:0|max:1000000000',
            'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
        ]);
        $data['invoice_no'] ??= '';
        if (! empty($data['purchase_order_id'])) {
            $order = PurchaseOrder::where('project_id', $projectId)->findOrFail($data['purchase_order_id']);
            $data['po_no'] = $order->po_no;
        }
        if (! empty($data['seller_id']) && (! $invoice || (int) $invoice->seller_id !== (int) $data['seller_id'])) {
            $seller = Seller::where('project_id', $projectId)->findOrFail($data['seller_id']);
            $data['seller_name'] = $seller->seller_name;
            $data['seller_no'] = 'SLR-'.str_pad((string) $seller->id, 3, '0', STR_PAD_LEFT);
            $data['seller_details'] = $seller->only(['contact_person', 'phone', 'email', 'website', 'address', 'tax_number', 'vendor_category']);
        }
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        return $data;
    }

    private function present(PurchaseInvoice $invoice): array
    {
        return $invoice->load(['creator:id,name', 'payments', 'purchaseOrder:id,po_no,po_date'])->toArray() + [
            'activities' => DB::table('purchase_invoice_activities')->where('purchase_invoice_id', $invoice->id)->latest('id')->get(),
            'attachments' => DB::table('purchase_invoice_attachments')->where('purchase_invoice_id', $invoice->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, PurchaseInvoice $invoice, string $action, array $changes = []): void
    {
        DB::table('purchase_invoice_activities')->insert([
            'purchase_invoice_id' => $invoice->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User',
            'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function ensureProject(Request $request, PurchaseInvoice $invoice): void
    {
        abort_unless((int) $invoice->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) PurchaseInvoice::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'PINV-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (PurchaseInvoice::where('project_id', $projectId)->where('invoice_no', $number)->exists());
        return $number;
    }
}
