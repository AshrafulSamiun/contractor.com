<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\CustomerCreditNote;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerCreditNoteController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = CustomerCreditNote::with(['creator:id,name', 'salesInvoice.salesOrder:id,order_no,order_date,estimation_id'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('credit_note_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('credit_note_no', 'like', "%{$term}%")
                ->orWhereHas('salesInvoice', fn ($invoice) => $invoice->where('invoice_no', 'like', "%{$term}%")
                    ->orWhere('customer_po_no', 'like', "%{$term}%")->orWhere('customer_details->name', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer')) $query->whereHas('salesInvoice', fn ($q) => $q->where('customer_details->name', $request->customer));
        if ($request->filled('from')) $query->whereDate('credit_note_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('credit_note_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, CustomerCreditNote $customerCreditNote)
    {
        $this->ensureProject($request, $customerCreditNote);
        return response()->json(['data' => $this->present($customerCreditNote)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $note = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['credit_note_no'] = $data['credit_note_no'] ?: $this->nextNumber($projectId);
            $note = CustomerCreditNote::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft']);
            $this->audit($request, $note, 'Created');
            return $note;
        });
        return response()->json(['data' => $this->present($note)], 201);
    }

    public function update(Request $request, CustomerCreditNote $customerCreditNote)
    {
        $this->ensureProject($request, $customerCreditNote);
        abort_if($customerCreditNote->status === 'Approved', 422, 'An approved credit note cannot be edited.');
        $data = $this->validated($request, $customerCreditNote->project_id, $customerCreditNote);
        DB::transaction(function () use ($request, $customerCreditNote, $data) {
            $note = CustomerCreditNote::whereKey($customerCreditNote->id)->lockForUpdate()->firstOrFail();
            $note->fill($data); $changes = array_keys($note->getDirty());
            if ($changes) { $note->status = 'Draft'; $note->save(); $this->audit($request, $note, 'Updated', $changes); }
        });
        return response()->json(['data' => $this->present($customerCreditNote->fresh())]);
    }

    public function destroy(Request $request, CustomerCreditNote $customerCreditNote)
    {
        $this->ensureProject($request, $customerCreditNote);
        abort_if($customerCreditNote->status === 'Approved', 422, 'An approved credit note cannot be deleted.');
        $paths = DB::table('customer_credit_note_attachments')->where('customer_credit_note_id', $customerCreditNote->id)->pluck('path')->all();
        $customerCreditNote->delete(); Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, CustomerCreditNote $customerCreditNote)
    {
        $this->ensureProject($request, $customerCreditNote);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        DB::transaction(function () use ($request, $customerCreditNote, $action) {
            $note = CustomerCreditNote::whereKey($customerCreditNote->id)->lockForUpdate()->firstOrFail();
            $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
            abort_unless(in_array($note->status, $allowed, true), 422, 'This approval action is no longer available.');
            if ($action === 'approve') $this->validateRemainingAmount($note->salesInvoice, (float) $note->total, $note);
            $note->status = ['submit' => 'Pending', 'approve' => 'Approved', 'reject' => 'Rejected'][$action];
            $note->save(); $this->audit($request, $note, $note->status);
        });
        return response()->json(['data' => $this->present($customerCreditNote->fresh())]);
    }

    public function attach(Request $request, CustomerCreditNote $customerCreditNote)
    {
        $this->ensureProject($request, $customerCreditNote);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file'); $path = $file->store('customer-credit-notes/'.$customerCreditNote->id, 'local');
        try {
            DB::table('customer_credit_note_attachments')->insert(['customer_credit_note_id' => $customerCreditNote->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $customerCreditNote, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($customerCreditNote->fresh())]);
    }

    public function download(Request $request, CustomerCreditNote $customerCreditNote, int $attachment)
    {
        $this->ensureProject($request, $customerCreditNote);
        $file = DB::table('customer_credit_note_attachments')->where('customer_credit_note_id', $customerCreditNote->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?CustomerCreditNote $note = null): array
    {
        $data = $request->validate([
            'credit_note_no' => [$note ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('customer_credit_notes')->where('project_id', $projectId)->ignore($note?->id)],
            'sales_invoice_id' => ['required', 'integer', Rule::exists('sales_invoices', 'id')->where('project_id', $projectId)],
            'credit_note_date' => 'required|date', 'reason' => 'required|string|max:255', 'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500', 'items.*.name' => 'required|string|max:255', 'items.*.description' => 'nullable|string|max:1000',
            'items.*.uom' => 'nullable|string|max:40', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000', 'items.*.unit_price' => 'required|numeric|min:0|max:1000000',
            'items.*.discount' => 'required|numeric|min:0|max:100', 'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);
        $data['credit_note_no'] ??= '';
        $invoice = SalesInvoice::where('project_id', $projectId)->findOrFail($data['sales_invoice_id']);
        abort_unless(in_array($invoice->status, ['Open', 'Paid'], true), 422, 'Only open or paid sales invoices can have credit notes.');
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price']), 2);
        $data['discount'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * $i['discount'] / 100), 2);
        $data['sales_tax'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * (1 - $i['discount'] / 100) * $i['tax_rate'] / 100), 2);
        $data['total'] = round($data['subtotal'] - $data['discount'] + $data['sales_tax'], 2);
        $this->validateRemainingAmount($invoice, $data['total'], $note);
        return $data;
    }

    private function validateRemainingAmount(SalesInvoice $invoice, float $amount, ?CustomerCreditNote $current = null): void
    {
        $credits = (float) $invoice->creditNotes()->where('status', 'Approved')->when($current, fn ($q) => $q->where('id', '!=', $current->id))->sum('total');
        $returns = (float) $invoice->returns()->where('status', 'Returned')->sum('total');
        if (round($credits + $returns + $amount, 2) > (float) $invoice->total) throw ValidationException::withMessages(['items' => 'Credits and returns exceed the remaining value of the sales invoice.']);
    }

    private function present(CustomerCreditNote $note): array
    {
        return $note->load(['creator:id,name', 'salesInvoice.returns', 'salesInvoice.salesOrder.estimation:id,estimation_no,issue_date'])->toArray() + ['activities' => DB::table('customer_credit_note_activities')->where('customer_credit_note_id', $note->id)->latest('id')->get(), 'attachments' => DB::table('customer_credit_note_attachments')->where('customer_credit_note_id', $note->id)->get(['id', 'name', 'size', 'created_at'])];
    }

    private function audit(Request $request, CustomerCreditNote $note, string $action, array $changes = []): void { DB::table('customer_credit_note_activities')->insert(['customer_credit_note_id' => $note->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]); }
    private function ensureProject(Request $request, CustomerCreditNote $note): void { abort_unless((int) $note->project_id === $this->accountSetupProjectId($request), 404); }
    private function nextNumber(int $projectId): string { $next = (int) CustomerCreditNote::where('project_id', $projectId)->max('id') + 1; do { $number = 'CCN-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); } while (CustomerCreditNote::where('project_id', $projectId)->where('credit_note_no', $number)->exists()); return $number; }
}
