<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\PurchaseInvoice;
use App\Models\SellerDebitNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerDebitNoteController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = SellerDebitNote::with(['creator:id,name', 'settlements', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('debit_note_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('debit_note_no', 'like', "%{$term}%")
                ->orWhereHas('purchaseInvoice', fn ($invoice) => $invoice->where('invoice_no', 'like', "%{$term}%")
                    ->orWhere('po_no', 'like', "%{$term}%")->orWhere('seller_name', 'like', "%{$term}%")));
        }
        foreach (['status', 'settlement_status'] as $field) if ($request->filled($field)) $query->where($field, $request->input($field));
        if ($request->filled('seller')) $query->whereHas('purchaseInvoice', fn ($q) => $q->where('seller_name', $request->seller));
        if ($request->filled('department')) $query->whereHas('purchaseInvoice', fn ($q) => $q->where('department', $request->department));
        if ($request->filled('project')) $query->whereHas('purchaseInvoice', fn ($q) => $q->where('project', $request->project));
        if ($request->filled('from')) $query->whereDate('debit_note_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('debit_note_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        return response()->json(['data' => $this->present($sellerDebitNote)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $note = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['debit_note_no'] = $data['debit_note_no'] ?: $this->nextNumber($projectId);
            abort_if(SellerDebitNote::where('project_id', $projectId)->where('debit_note_no', $data['debit_note_no'])->exists(), 422, 'This debit note number is already in use.');
            $note = SellerDebitNote::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft', 'settlement_status' => 'Open', 'settled' => 0]);
            $this->audit($request, $note, 'Created');
            return $note;
        });
        return response()->json(['data' => $this->present($note)], 201);
    }

    public function update(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        abort_if((float) $sellerDebitNote->settled > 0, 422, 'A debit note with settlements cannot be edited.');
        $data = $this->validated($request, $sellerDebitNote->project_id, $sellerDebitNote);
        DB::transaction(function () use ($request, $sellerDebitNote, $data) {
            $note = SellerDebitNote::whereKey($sellerDebitNote->id)->lockForUpdate()->firstOrFail();
            $note->fill($data); $changes = array_keys($note->getDirty());
            if ($changes) { $note->status = 'Draft'; $note->save(); $this->audit($request, $note, 'Updated', $changes); }
        });
        return response()->json(['data' => $this->present($sellerDebitNote->fresh())]);
    }

    public function destroy(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        abort_if((float) $sellerDebitNote->settled > 0, 422, 'A debit note with settlements cannot be deleted.');
        $paths = DB::table('seller_debit_note_attachments')->where('seller_debit_note_id', $sellerDebitNote->id)->pluck('path')->all();
        $sellerDebitNote->delete(); Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject,close'])['action'];
        DB::transaction(function () use ($request, $sellerDebitNote, $action) {
            $note = SellerDebitNote::whereKey($sellerDebitNote->id)->lockForUpdate()->firstOrFail();
            if ($action === 'close') {
                abort_unless($note->status === 'Approved' && (float) $note->settled >= (float) $note->total, 422, 'Fully settle the approved debit note before closing it.');
                $note->status = 'Closed';
            } else {
                $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
                abort_unless(in_array($note->status, $allowed), 422, 'This approval action is no longer available.');
                if ($action === 'approve') $this->validateRemainingAmount($note->purchaseInvoice, (float) $note->total, $note);
                $note->status = ['submit' => 'Pending', 'approve' => 'Approved', 'reject' => 'Rejected'][$action];
            }
            $note->save(); $this->audit($request, $note, $note->status);
        });
        return response()->json(['data' => $this->present($sellerDebitNote->fresh())]);
    }

    public function settle(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        $data = $request->validate([
            'settlement_no' => ['required', 'string', 'max:100', Rule::unique('seller_debit_note_settlements')->where('seller_debit_note_id', $sellerDebitNote->id)],
            'settlement_date' => 'required|date', 'amount' => 'required|numeric|gt:0|max:999999999999',
            'method' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000',
        ]);
        DB::transaction(function () use ($request, $sellerDebitNote, $data) {
            $note = SellerDebitNote::whereKey($sellerDebitNote->id)->lockForUpdate()->firstOrFail();
            abort_unless($note->status === 'Approved', 422, 'Approve the debit note before settlement.');
            $balance = round((float) $note->total - (float) $note->settled, 2);
            abort_if((float) $data['amount'] > $balance, 422, 'Settlement amount exceeds the debit note balance.');
            $note->settlements()->create($data);
            $note->settled = round((float) $note->settled + (float) $data['amount'], 2);
            $note->settlement_status = (float) $note->settled >= (float) $note->total ? 'Paid' : 'Partial';
            $note->save(); $this->audit($request, $note, 'Settlement recorded', ['number' => $data['settlement_no'], 'amount' => $data['amount']]);
        });
        return response()->json(['data' => $this->present($sellerDebitNote->fresh())]);
    }

    public function attach(Request $request, SellerDebitNote $sellerDebitNote)
    {
        $this->ensureProject($request, $sellerDebitNote);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file'); $path = $file->store('seller-debit-notes/'.$sellerDebitNote->id, 'local');
        try {
            DB::table('seller_debit_note_attachments')->insert(['seller_debit_note_id' => $sellerDebitNote->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $sellerDebitNote, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($sellerDebitNote->fresh())]);
    }

    public function download(Request $request, SellerDebitNote $sellerDebitNote, int $attachment)
    {
        $this->ensureProject($request, $sellerDebitNote);
        $file = DB::table('seller_debit_note_attachments')->where('seller_debit_note_id', $sellerDebitNote->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?SellerDebitNote $note = null): array
    {
        $data = $request->validate([
            'debit_note_no' => [$note ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('seller_debit_notes')->where('project_id', $projectId)->ignore($note?->id)],
            'purchase_invoice_id' => ['required', 'integer', Rule::exists('purchase_invoices', 'id')->where('project_id', $projectId)],
            'debit_note_date' => 'required|date', 'due_date' => 'nullable|date|after_or_equal:debit_note_date',
            'reason' => 'required|string|max:255', 'notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500', 'items.*.invoice_item_index' => 'nullable|integer|min:0',
            'items.*.name' => 'required|string|max:255', 'items.*.code' => 'nullable|string|max:100', 'items.*.uom' => 'nullable|string|max:40',
            'items.*.quantity' => 'required|numeric|gt:0|max:1000000', 'items.*.cost_rate' => 'required|numeric|min:0|max:1000000',
            'items.*.sales_tax' => 'required|numeric|min:0|max:1000000000',
        ]);
        $data['debit_note_no'] ??= '';
        $invoice = PurchaseInvoice::where('project_id', $projectId)->findOrFail($data['purchase_invoice_id']);
        abort_if($invoice->status !== 'Approved', 422, 'Only approved purchase invoices can have debit notes.');
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        $this->validateRemainingAmount($invoice, $data['total'], $note);
        return $data;
    }

    private function validateRemainingAmount(PurchaseInvoice $invoice, float $amount, ?SellerDebitNote $current = null): void
    {
        $used = (float) $invoice->debitNotes()->whereIn('status', ['Approved', 'Closed'])->when($current, fn ($q) => $q->where('id', '!=', $current->id))->sum('total');
        if (round($used + $amount, 2) > (float) $invoice->total) throw ValidationException::withMessages(['items' => 'Debit notes exceed the remaining value of the original invoice.']);
    }

    private function present(SellerDebitNote $note): array
    {
        return $note->load(['creator:id,name', 'settlements', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])->toArray() + [
            'activities' => DB::table('seller_debit_note_activities')->where('seller_debit_note_id', $note->id)->latest('id')->get(),
            'attachments' => DB::table('seller_debit_note_attachments')->where('seller_debit_note_id', $note->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, SellerDebitNote $note, string $action, array $changes = []): void
    {
        DB::table('seller_debit_note_activities')->insert(['seller_debit_note_id' => $note->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function ensureProject(Request $request, SellerDebitNote $note): void
    {
        abort_unless((int) $note->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) SellerDebitNote::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'SDN-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (SellerDebitNote::where('project_id', $projectId)->where('debit_note_no', $number)->exists());
        return $number;
    }
}
