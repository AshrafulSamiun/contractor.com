<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalesReturnController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = SalesReturn::with(['creator:id,name', 'refunds', 'salesInvoice.salesOrder:id,order_no,order_date,estimation_id'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('return_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('return_no', 'like', "%{$term}%")
                ->orWhereHas('salesInvoice', fn ($invoice) => $invoice->where('invoice_no', 'like', "%{$term}%")
                    ->orWhere('customer_details->name', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('refund_status')) $query->where('refund_status', $request->refund_status);
        if ($request->filled('from')) $query->whereDate('return_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('return_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        return response()->json(['data' => $this->present($salesReturn)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $return = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['return_no'] = $data['return_no'] ?: $this->nextNumber($projectId);
            $return = SalesReturn::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft', 'refund_status' => 'Unpaid', 'refunded' => 0]);
            $this->audit($request, $return, 'Created');
            return $return;
        });
        return response()->json(['data' => $this->present($return)], 201);
    }

    public function update(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        abort_if((float) $salesReturn->refunded > 0, 422, 'A return with refunds cannot be edited.');
        $data = $this->validated($request, $salesReturn->project_id, $salesReturn);
        DB::transaction(function () use ($request, $salesReturn, $data) {
            $return = SalesReturn::whereKey($salesReturn->id)->lockForUpdate()->firstOrFail();
            $return->fill($data);
            $changes = array_keys($return->getDirty());
            if ($changes) { $return->status = 'Draft'; $return->save(); $this->audit($request, $return, 'Updated', $changes); }
        });
        return response()->json(['data' => $this->present($salesReturn->fresh())]);
    }

    public function destroy(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        abort_if((float) $salesReturn->refunded > 0, 422, 'A return with refunds cannot be deleted.');
        $paths = DB::table('sales_return_attachments')->where('sales_return_id', $salesReturn->id)->pluck('path')->all();
        $salesReturn->delete();
        Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        DB::transaction(function () use ($request, $salesReturn, $action) {
            $return = SalesReturn::whereKey($salesReturn->id)->lockForUpdate()->firstOrFail();
            $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Pending'];
            abort_unless(in_array($return->status, $allowed, true), 422, 'This approval action is no longer available.');
            if ($action === 'approve') $this->validateAvailableQuantities($return->salesInvoice, $return->items, $return);
            $return->status = ['submit' => 'Pending', 'approve' => 'Returned', 'reject' => 'Rejected'][$action];
            $return->save();
            $this->audit($request, $return, $return->status);
        });
        return response()->json(['data' => $this->present($salesReturn->fresh())]);
    }

    public function refund(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        $data = $request->validate(['refund_no' => 'required|string|max:100', 'refund_date' => 'required|date', 'amount' => 'required|numeric|gt:0', 'method' => 'nullable|string|max:100', 'reference' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000']);
        DB::transaction(function () use ($request, $salesReturn, $data) {
            $return = SalesReturn::whereKey($salesReturn->id)->lockForUpdate()->firstOrFail();
            abort_unless($return->status === 'Returned', 422, 'Approve the return before recording a customer refund.');
            $balance = round((float) $return->total - (float) $return->refunded, 2);
            abort_if((float) $data['amount'] > $balance, 422, 'Refund amount exceeds the return balance.');
            $return->refunds()->create($data);
            $return->refunded = round((float) $return->refunded + (float) $data['amount'], 2);
            $return->refund_status = $return->refunded >= $return->total ? 'Paid' : 'Partial';
            $return->save();
            $this->audit($request, $return, 'Customer refund recorded', ['number' => $data['refund_no'], 'amount' => $data['amount']]);
        });
        return response()->json(['data' => $this->present($salesReturn->fresh())]);
    }

    public function attach(Request $request, SalesReturn $salesReturn)
    {
        $this->ensureProject($request, $salesReturn);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file');
        $path = $file->store('sales-returns/'.$salesReturn->id, 'local');
        DB::table('sales_return_attachments')->insert(['sales_return_id' => $salesReturn->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
        $this->audit($request, $salesReturn, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
        return response()->json(['data' => $this->present($salesReturn->fresh())]);
    }

    public function download(Request $request, SalesReturn $salesReturn, int $attachment)
    {
        $this->ensureProject($request, $salesReturn);
        $file = DB::table('sales_return_attachments')->where('sales_return_id', $salesReturn->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?SalesReturn $return = null): array
    {
        $data = $request->validate([
            'return_no' => [$return ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('sales_returns')->where('project_id', $projectId)->ignore($return?->id)],
            'sales_invoice_id' => ['required', 'integer', Rule::exists('sales_invoices', 'id')->where('project_id', $projectId)],
            'return_date' => 'required|date', 'expected_pickup_date' => 'nullable|date|after_or_equal:return_date',
            'reason' => 'required|string|max:255', 'return_type' => 'nullable|string|max:100', 'warehouse' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:255', 'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500', 'items.*.invoice_item_index' => 'required|integer|min:0', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000',
        ]);
        $data['return_no'] ??= '';
        $invoice = SalesInvoice::where('project_id', $projectId)->findOrFail($data['sales_invoice_id']);
        abort_unless(in_array($invoice->status, ['Open', 'Paid'], true), 422, 'Only open or paid sales invoices can be returned.');
        $this->validateAvailableQuantities($invoice, $data['items'], $return);
        $data['items'] = collect($data['items'])->map(function ($item) use ($invoice) {
            $source = $invoice->items[(int) $item['invoice_item_index']];
            return ['invoice_item_index' => (int) $item['invoice_item_index'], 'name' => $source['name'], 'description' => $source['description'] ?? '', 'uom' => $source['uom'] ?? 'PCS', 'quantity' => (float) $item['quantity'], 'unit_price' => (float) $source['unit_price'], 'discount' => (float) ($source['discount'] ?? 0), 'tax_rate' => (float) ($source['tax_rate'] ?? 0)];
        })->values()->all();
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price']), 2);
        $data['discount'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * $i['discount'] / 100), 2);
        $data['sales_tax'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * (1 - $i['discount'] / 100) * $i['tax_rate'] / 100), 2);
        $data['total'] = round($data['subtotal'] - $data['discount'] + $data['sales_tax'], 2);
        return $data;
    }

    private function validateAvailableQuantities(SalesInvoice $invoice, array $items, ?SalesReturn $current = null): void
    {
        $used = $invoice->returns()->where('status', 'Returned')->when($current, fn ($q) => $q->where('id', '!=', $current->id))->get()->flatMap(fn ($r) => $r->items)->groupBy('invoice_item_index')->map(fn ($rows) => $rows->sum('quantity'));
        foreach (collect($items)->groupBy('invoice_item_index') as $index => $rows) {
            $source = $invoice->items[(int) $index] ?? null;
            if (! $source || $rows->sum('quantity') + (float) ($used[$index] ?? 0) > (float) $source['quantity']) throw ValidationException::withMessages(['items' => 'A return quantity exceeds the remaining quantity on the sales invoice.']);
        }
    }

    private function present(SalesReturn $return): array
    {
        return $return->load(['creator:id,name', 'refunds', 'salesInvoice.salesOrder.estimation:id,estimation_no,issue_date'])->toArray() + ['activities' => DB::table('sales_return_activities')->where('sales_return_id', $return->id)->latest('id')->get(), 'attachments' => DB::table('sales_return_attachments')->where('sales_return_id', $return->id)->get(['id', 'name', 'size', 'created_at'])];
    }

    private function audit(Request $request, SalesReturn $return, string $action, array $changes = []): void
    {
        DB::table('sales_return_activities')->insert(['sales_return_id' => $return->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function ensureProject(Request $request, SalesReturn $return): void { abort_unless((int) $return->project_id === $this->accountSetupProjectId($request), 404); }
    private function nextNumber(int $projectId): string { $next = (int) SalesReturn::where('project_id', $projectId)->max('id') + 1; do { $number = 'SIR-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); } while (SalesReturn::where('project_id', $projectId)->where('return_no', $number)->exists()); return $number; }
}
