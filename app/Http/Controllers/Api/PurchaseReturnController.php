<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['creator:id,name', 'refunds', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('return_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('return_no', 'like', "%{$term}%")
                ->orWhereHas('purchaseInvoice', fn ($invoice) => $invoice->where('invoice_no', 'like', "%{$term}%")
                    ->orWhere('seller_name', 'like', "%{$term}%")->orWhere('po_no', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('refund_status')) $query->where('refund_status', $request->refund_status);
        if ($request->filled('seller')) $query->whereHas('purchaseInvoice', fn ($q) => $q->where('seller_name', $request->seller));
        if ($request->filled('from')) $query->whereDate('return_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('return_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        return response()->json(['data' => $this->present($purchaseReturn)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $return = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['return_no'] = $data['return_no'] ?: $this->nextNumber($projectId);
            abort_if(PurchaseReturn::where('project_id', $projectId)->where('return_no', $data['return_no'])->exists(), 422, 'This return number is already in use.');
            $return = PurchaseReturn::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft', 'refund_status' => 'Unpaid', 'refunded' => 0]);
            $this->audit($request, $return, 'Created');
            return $return;
        });
        return response()->json(['data' => $this->present($return)], 201);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        abort_if((float) $purchaseReturn->refunded > 0, 422, 'A return with refunds cannot be edited.');
        $data = $this->validated($request, $purchaseReturn->project_id, $purchaseReturn);
        DB::transaction(function () use ($request, $purchaseReturn, $data) {
            $return = PurchaseReturn::whereKey($purchaseReturn->id)->lockForUpdate()->firstOrFail();
            $return->fill($data);
            $changes = array_keys($return->getDirty());
            if ($changes) { $return->status = 'Draft'; $return->save(); $this->audit($request, $return, 'Updated', $changes); }
        });
        return response()->json(['data' => $this->present($purchaseReturn->fresh())]);
    }

    public function destroy(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        abort_if((float) $purchaseReturn->refunded > 0, 422, 'A return with refunds cannot be deleted.');
        $paths = DB::table('purchase_return_attachments')->where('purchase_return_id', $purchaseReturn->id)->pluck('path')->all();
        $purchaseReturn->delete();
        Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        DB::transaction(function () use ($request, $purchaseReturn, $action) {
            $return = PurchaseReturn::whereKey($purchaseReturn->id)->lockForUpdate()->firstOrFail();
            $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
            abort_unless(in_array($return->status, $allowed), 422, 'This approval action is no longer available.');
            if ($action === 'approve') $this->validateAvailableQuantities($return->purchaseInvoice, $return->items, $return);
            $return->status = ['submit' => 'Pending', 'approve' => 'Returned', 'reject' => 'Rejected'][$action];
            $return->save();
            $this->audit($request, $return, $return->status);
        });
        return response()->json(['data' => $this->present($purchaseReturn->fresh())]);
    }

    public function refund(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        $data = $request->validate([
            'refund_no' => ['required', 'string', 'max:100', Rule::unique('purchase_return_refunds')->where('purchase_return_id', $purchaseReturn->id)],
            'refund_date' => 'required|date', 'amount' => 'required|numeric|gt:0|max:999999999999',
            'method' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000',
        ]);
        DB::transaction(function () use ($request, $purchaseReturn, $data) {
            $return = PurchaseReturn::whereKey($purchaseReturn->id)->lockForUpdate()->firstOrFail();
            abort_unless($return->status === 'Returned', 422, 'Approve the return before recording a refund.');
            $balance = round((float) $return->total - (float) $return->refunded, 2);
            abort_if((float) $data['amount'] > $balance, 422, 'Refund amount exceeds the outstanding return balance.');
            $return->refunds()->create($data);
            $return->refunded = round((float) $return->refunded + (float) $data['amount'], 2);
            $return->refund_status = (float) $return->refunded >= (float) $return->total ? 'Paid' : 'Partial';
            $return->save();
            $this->audit($request, $return, 'Refund received', ['number' => $data['refund_no'], 'amount' => $data['amount']]);
        });
        return response()->json(['data' => $this->present($purchaseReturn->fresh())]);
    }

    public function attach(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->ensureProject($request, $purchaseReturn);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file');
        $path = $file->store('purchase-returns/'.$purchaseReturn->id, 'local');
        try {
            DB::table('purchase_return_attachments')->insert(['purchase_return_id' => $purchaseReturn->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $purchaseReturn, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($purchaseReturn->fresh())]);
    }

    public function download(Request $request, PurchaseReturn $purchaseReturn, int $attachment)
    {
        $this->ensureProject($request, $purchaseReturn);
        $file = DB::table('purchase_return_attachments')->where('purchase_return_id', $purchaseReturn->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?PurchaseReturn $return = null): array
    {
        $data = $request->validate([
            'return_no' => [$return ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('purchase_returns')->where('project_id', $projectId)->ignore($return?->id)],
            'purchase_invoice_id' => ['required', 'integer', Rule::exists('purchase_invoices', 'id')->where('project_id', $projectId)],
            'return_date' => 'required|date', 'return_due_date' => 'nullable|date|after_or_equal:return_date',
            'reason' => 'required|string|max:255', 'notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500', 'items.*.invoice_item_index' => 'required|integer|min:0',
            'items.*.name' => 'required|string|max:255', 'items.*.code' => 'nullable|string|max:100', 'items.*.uom' => 'nullable|string|max:40',
            'items.*.quantity' => 'required|numeric|gt:0|max:1000000', 'items.*.cost_rate' => 'nullable|numeric|min:0|max:1000000',
            'items.*.sales_tax' => 'nullable|numeric|min:0|max:1000000000',
        ]);
        $data['return_no'] ??= '';
        $invoice = PurchaseInvoice::where('project_id', $projectId)->findOrFail($data['purchase_invoice_id']);
        abort_if($invoice->status !== 'Approved', 422, 'Only approved purchase invoices can be returned.');
        $this->validateAvailableQuantities($invoice, $data['items'], $return);
        $data['items'] = collect($data['items'])->map(function ($item) use ($invoice) {
            $source = $invoice->items[(int) $item['invoice_item_index']];
            $ratio = (float) $item['quantity'] / (float) $source['quantity'];
            return [
                'invoice_item_index' => (int) $item['invoice_item_index'], 'name' => $source['name'],
                'code' => $source['code'] ?? '', 'uom' => $source['uom'] ?? 'PCS',
                'quantity' => (float) $item['quantity'], 'cost_rate' => (float) $source['cost_rate'],
                'sales_tax' => round((float) ($source['sales_tax'] ?? 0) * $ratio, 2),
            ];
        })->values()->all();
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        return $data;
    }

    private function validateAvailableQuantities(PurchaseInvoice $invoice, array $items, ?PurchaseReturn $current = null): void
    {
        $alreadyReturned = $invoice->returns()->where('status', 'Returned')->when($current, fn ($q) => $q->where('id', '!=', $current->id))->get()->flatMap(fn ($return) => $return->items)
            ->groupBy('invoice_item_index')->map(fn ($rows) => $rows->sum('quantity'));
        foreach (collect($items)->groupBy('invoice_item_index') as $index => $returnItems) {
            $index = (int) $index;
            $source = $invoice->items[$index] ?? null;
            $requested = $returnItems->sum('quantity');
            if (! $source || $requested + (float) ($alreadyReturned[$index] ?? 0) > (float) $source['quantity']) {
                throw ValidationException::withMessages(['items' => 'A return quantity exceeds the remaining quantity on the original invoice.']);
            }
        }
    }

    private function present(PurchaseReturn $return): array
    {
        return $return->load(['creator:id,name', 'refunds', 'purchaseInvoice.purchaseOrder:id,po_no,po_date'])->toArray() + [
            'activities' => DB::table('purchase_return_activities')->where('purchase_return_id', $return->id)->latest('id')->get(),
            'attachments' => DB::table('purchase_return_attachments')->where('purchase_return_id', $return->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, PurchaseReturn $return, string $action, array $changes = []): void
    {
        DB::table('purchase_return_activities')->insert(['purchase_return_id' => $return->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function ensureProject(Request $request, PurchaseReturn $return): void
    {
        abort_unless((int) $return->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) PurchaseReturn::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'PRT-'.now()->format('Y').'-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (PurchaseReturn::where('project_id', $projectId)->where('return_no', $number)->exists());
        return $number;
    }
}
