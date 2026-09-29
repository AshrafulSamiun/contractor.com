<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\PurchaseInvoice;
use App\Models\SellerCreditNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerCreditNoteController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = SellerCreditNote::with(['creator:id,name', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('credit_note_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('credit_note_no', 'like', "%{$term}%")
                ->orWhereHas('purchaseInvoice', fn ($invoice) => $invoice->where('invoice_no', 'like', "%{$term}%")
                    ->orWhere('po_no', 'like', "%{$term}%")->orWhere('seller_name', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('seller')) $query->whereHas('purchaseInvoice', fn ($q) => $q->where('seller_name', $request->seller));
        if ($request->filled('from')) $query->whereDate('credit_note_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('credit_note_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, SellerCreditNote $sellerCreditNote)
    {
        $this->ensureProject($request, $sellerCreditNote);
        return response()->json(['data' => $this->present($sellerCreditNote)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $note = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['credit_note_no'] = $data['credit_note_no'] ?: $this->nextNumber($projectId);
            abort_if(SellerCreditNote::where('project_id', $projectId)->where('credit_note_no', $data['credit_note_no'])->exists(), 422, 'This credit note number is already in use.');
            $note = SellerCreditNote::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft']);
            $this->audit($request, $note, 'Created');
            return $note;
        });
        return response()->json(['data' => $this->present($note)], 201);
    }

    public function update(Request $request, SellerCreditNote $sellerCreditNote)
    {
        $this->ensureProject($request, $sellerCreditNote);
        abort_if($sellerCreditNote->status === 'Approved', 422, 'An approved credit note cannot be edited.');
        $data = $this->validated($request, $sellerCreditNote->project_id, $sellerCreditNote);
        DB::transaction(function () use ($request, $sellerCreditNote, $data) {
            $note = SellerCreditNote::whereKey($sellerCreditNote->id)->lockForUpdate()->firstOrFail();
            $note->fill($data); $changes = array_keys($note->getDirty());
            if ($changes) { $note->status = 'Draft'; $note->save(); $this->audit($request, $note, 'Updated', $changes); }
        });
        return response()->json(['data' => $this->present($sellerCreditNote->fresh())]);
    }

    public function destroy(Request $request, SellerCreditNote $sellerCreditNote)
    {
        $this->ensureProject($request, $sellerCreditNote);
        abort_if($sellerCreditNote->status === 'Approved', 422, 'An approved credit note cannot be deleted.');
        $paths = DB::table('seller_credit_note_attachments')->where('seller_credit_note_id', $sellerCreditNote->id)->pluck('path')->all();
        $sellerCreditNote->delete(); Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, SellerCreditNote $sellerCreditNote)
    {
        $this->ensureProject($request, $sellerCreditNote);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        DB::transaction(function () use ($request, $sellerCreditNote, $action) {
            $note = SellerCreditNote::whereKey($sellerCreditNote->id)->lockForUpdate()->firstOrFail();
            $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
            abort_unless(in_array($note->status, $allowed), 422, 'This approval action is no longer available.');
            if ($action === 'approve') $this->validateRemainingAmount($note->purchaseInvoice, (float) $note->total, $note);
            $note->status = ['submit' => 'Pending', 'approve' => 'Approved', 'reject' => 'Rejected'][$action];
            $note->save(); $this->audit($request, $note, $note->status);
        });
        return response()->json(['data' => $this->present($sellerCreditNote->fresh())]);
    }

    public function attach(Request $request, SellerCreditNote $sellerCreditNote)
    {
        $this->ensureProject($request, $sellerCreditNote);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file'); $path = $file->store('seller-credit-notes/'.$sellerCreditNote->id, 'local');
        try {
            DB::table('seller_credit_note_attachments')->insert(['seller_credit_note_id' => $sellerCreditNote->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $sellerCreditNote, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($sellerCreditNote->fresh())]);
    }

    public function download(Request $request, SellerCreditNote $sellerCreditNote, int $attachment)
    {
        $this->ensureProject($request, $sellerCreditNote);
        $file = DB::table('seller_credit_note_attachments')->where('seller_credit_note_id', $sellerCreditNote->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?SellerCreditNote $note = null): array
    {
        $data = $request->validate([
            'credit_note_no' => [$note ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('seller_credit_notes')->where('project_id', $projectId)->ignore($note?->id)],
            'purchase_invoice_id' => ['required', 'integer', Rule::exists('purchase_invoices', 'id')->where('project_id', $projectId)],
            'credit_note_date' => 'required|date', 'due_date' => 'nullable|date|after_or_equal:credit_note_date',
            'reason' => 'required|string|max:255', 'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500', 'items.*.invoice_item_index' => 'nullable|integer|min:0',
            'items.*.name' => 'required|string|max:255', 'items.*.code' => 'nullable|string|max:100', 'items.*.uom' => 'nullable|string|max:40',
            'items.*.quantity' => 'required|numeric|gt:0|max:1000000', 'items.*.cost_rate' => 'required|numeric|min:0|max:1000000',
            'items.*.sales_tax' => 'required|numeric|min:0|max:1000000000',
        ]);
        $data['credit_note_no'] ??= '';
        $invoice = PurchaseInvoice::where('project_id', $projectId)->findOrFail($data['purchase_invoice_id']);
        abort_if($invoice->status !== 'Approved', 422, 'Only approved purchase invoices can have credit notes.');
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        $this->validateRemainingAmount($invoice, $data['total'], $note);
        return $data;
    }

    private function validateRemainingAmount(PurchaseInvoice $invoice, float $amount, ?SellerCreditNote $current = null): void
    {
        $credits = (float) $invoice->creditNotes()->where('status', 'Approved')->when($current, fn ($q) => $q->where('id', '!=', $current->id))->sum('total');
        $returns = (float) $invoice->returns()->where('status', 'Returned')->sum('total');
        if (round($credits + $returns + $amount, 2) > (float) $invoice->total) throw ValidationException::withMessages(['items' => 'Credits and returns exceed the remaining value of the original invoice.']);
    }

    private function present(SellerCreditNote $note): array
    {
        return $note->load(['creator:id,name', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])->toArray() + [
            'activities' => DB::table('seller_credit_note_activities')->where('seller_credit_note_id', $note->id)->latest('id')->get(),
            'attachments' => DB::table('seller_credit_note_attachments')->where('seller_credit_note_id', $note->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, SellerCreditNote $note, string $action, array $changes = []): void
    {
        DB::table('seller_credit_note_activities')->insert(['seller_credit_note_id' => $note->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function ensureProject(Request $request, SellerCreditNote $note): void
    {
        abort_unless((int) $note->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) SellerCreditNote::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'CN-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (SellerCreditNote::where('project_id', $projectId)->where('credit_note_no', $number)->exists());
        return $number;
    }
}
