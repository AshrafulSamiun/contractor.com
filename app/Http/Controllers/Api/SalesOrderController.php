<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountHolder;
use App\Models\AccountSetup;
use App\Models\Estimation;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    use ResolvesAccountSetupProject;

    public function options(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $customers = AccountHolder::where('project_id', $projectId)->where('account_type', 1)->where('is_deleted', false)->where('status_active', true)->orderBy('company_name')->get()->map(fn ($customer) => $this->customerDetails($customer));
        $estimates = Estimation::with(['customer', 'details'])->where('status', 2)->whereHas('customer', fn ($q) => $q->where('project_id', $projectId))->latest('issue_date')->get()->map(fn ($estimate) => [
            'id' => $estimate->id, 'estimation_no' => $estimate->estimation_no, 'customer_id' => $estimate->customer_id,
            'issue_date' => $estimate->issue_date?->toDateString(), 'expire_date' => $estimate->expire_date?->toDateString(),
            'currency_code' => $estimate->currency_label, 'payment_terms' => $estimate->payment_term,
            'items' => $estimate->details->map(fn ($item) => ['name' => $item->item_name, 'description' => $item->item_description, 'uom' => 'LS', 'quantity' => (float) $item->quantity, 'unit_price' => (float) $item->unit_price, 'discount' => 0, 'tax_rate' => (float) $item->sale_tax_percentage])->all(),
            'terms' => $estimate->scope_of_work, 'notes' => $estimate->notes_to_customer ?: $estimate->note,
        ]);
        return response()->json(['data' => ['customers' => $customers, 'estimates' => $estimates]]);
    }

    public function index(Request $request)
    {
        $query = SalesOrder::with(['creator:id,name', 'estimation:id,estimation_no,issue_date'])->where('project_id', $this->accountSetupProjectId($request))->latest('order_date')->latest('id');
        if ($request->filled('search')) { $term = $request->string('search')->toString(); $query->where(fn ($q) => $q->where('order_no', 'like', "%{$term}%")->orWhere('customer_po_no', 'like', "%{$term}%")->orWhere('customer_details->name', 'like', "%{$term}%")); }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, SalesOrder $salesOrder)
    {
        $this->ensureProject($request, $salesOrder);
        return response()->json(['data' => $this->present($salesOrder)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request); $data = $this->validated($request, $projectId);
        $order = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['order_no'] = $data['order_no'] ?: $this->nextNumber($projectId);
            abort_if(SalesOrder::where('project_id', $projectId)->where('order_no', $data['order_no'])->exists(), 422, 'This sales order number is already in use.');
            $order = SalesOrder::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id, 'status' => 'Draft']);
            $this->audit($request, $order, 'Created'); return $order;
        });
        return response()->json(['data' => $this->present($order)], 201);
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $this->ensureProject($request, $salesOrder); abort_if(in_array($salesOrder->status, ['Confirmed', 'Completed']), 422, 'A confirmed sales order cannot be edited.');
        $data = $this->validated($request, $salesOrder->project_id, $salesOrder);
        DB::transaction(function () use ($request, $salesOrder, $data) { $order = SalesOrder::whereKey($salesOrder->id)->lockForUpdate()->firstOrFail(); $order->fill($data); $changes = array_keys($order->getDirty()); if ($changes) { $order->status = 'Draft'; $order->save(); $this->audit($request, $order, 'Updated', $changes); } });
        return response()->json(['data' => $this->present($salesOrder->fresh())]);
    }

    public function destroy(Request $request, SalesOrder $salesOrder)
    {
        $this->ensureProject($request, $salesOrder); abort_if(in_array($salesOrder->status, ['Confirmed', 'Completed']), 422, 'A confirmed sales order cannot be deleted.');
        $paths = DB::table('sales_order_attachments')->where('sales_order_id', $salesOrder->id)->pluck('path')->all(); $salesOrder->delete(); Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, SalesOrder $salesOrder)
    {
        $this->ensureProject($request, $salesOrder); $action = $request->validate(['action' => 'required|in:submit,confirm,reject,complete'])['action'];
        DB::transaction(function () use ($request, $salesOrder, $action) {
            $order = SalesOrder::whereKey($salesOrder->id)->lockForUpdate()->firstOrFail();
            $allowed = ['submit' => ['Draft', 'Rejected'], 'confirm' => ['Draft', 'Pending Approval'], 'reject' => ['Pending Approval'], 'complete' => ['Confirmed']][$action];
            abort_unless(in_array($order->status, $allowed), 422, 'This workflow action is no longer available.');
            $order->status = ['submit' => 'Pending Approval', 'confirm' => 'Confirmed', 'reject' => 'Rejected', 'complete' => 'Completed'][$action]; $order->save(); $this->audit($request, $order, $order->status);
        });
        return response()->json(['data' => $this->present($salesOrder->fresh())]);
    }

    public function attach(Request $request, SalesOrder $salesOrder)
    {
        $this->ensureProject($request, $salesOrder); $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file'); $path = $file->store('sales-orders/'.$salesOrder->id, 'local');
        try { DB::table('sales_order_attachments')->insert(['sales_order_id' => $salesOrder->id, 'name' => basename($file->getClientOriginalName()), 'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]); $this->audit($request, $salesOrder, 'Attached file', ['name' => basename($file->getClientOriginalName())]); } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return response()->json(['data' => $this->present($salesOrder->fresh())]);
    }

    public function download(Request $request, SalesOrder $salesOrder, int $attachment)
    {
        $this->ensureProject($request, $salesOrder); $file = DB::table('sales_order_attachments')->where('sales_order_id', $salesOrder->id)->where('id', $attachment)->first(); abort_unless($file, 404); return Storage::disk('local')->download($file->path, $file->name);
    }

    private function validated(Request $request, int $projectId, ?SalesOrder $order = null): array
    {
        $data = $request->validate([
            'order_no' => [$order ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('sales_orders')->where('project_id', $projectId)->ignore($order?->id)],
            'customer_id' => ['required', 'integer', Rule::exists('account_holders', 'id')->where(fn ($q) => $q->where('project_id', $projectId)->where('account_type', 1))],
            'estimation_id' => 'nullable|integer|exists:estimations,id', 'order_date' => 'required|date', 'valid_until' => 'nullable|date|after_or_equal:order_date',
            'customer_po_no' => 'nullable|string|max:100', 'salesperson' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255',
            'currency_code' => 'required|string|size:3', 'exchange_rate' => 'required|numeric|gt:0', 'price_list' => 'nullable|string|max:255', 'sales_channel' => 'nullable|string|max:100',
            'delivery_location' => 'nullable|string|max:255', 'shipping_method' => 'nullable|string|max:100', 'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_terms' => 'nullable|string|max:255', 'payment_terms' => 'nullable|string|max:100', 'reference' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:500', 'items.*.name' => 'required|string|max:255', 'items.*.description' => 'nullable|string|max:1000', 'items.*.uom' => 'nullable|string|max:40',
            'items.*.quantity' => 'required|numeric|gt:0|max:1000000', 'items.*.unit_price' => 'required|numeric|min:0|max:1000000', 'items.*.discount' => 'required|numeric|min:0|max:100', 'items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
        ]);
        $data['order_no'] ??= ''; $customer = AccountHolder::where('project_id', $projectId)->where('account_type', 1)->findOrFail($data['customer_id']); $data['customer_details'] = $this->customerDetails($customer);
        $items = collect($data['items']); $data['subtotal'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price']), 2); $data['discount'] = round($items->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * $i['discount'] / 100), 2); $data['sales_tax'] = round($items->sum(fn ($i) => ($i['quantity'] * $i['unit_price'] * (1 - $i['discount'] / 100)) * $i['tax_rate'] / 100), 2); $data['total'] = $data['subtotal'] - $data['discount'] + $data['sales_tax'];
        return $data;
    }

    private function customerDetails(AccountHolder $c): array { return ['id' => $c->id, 'customer_no' => $c->system_no, 'name' => $c->company_name ?: $c->account_name, 'contact_person' => $c->primary_contact_name ?: $c->account_name, 'phone' => $c->cell_phone ?: $c->office_phone, 'email' => $c->email, 'address' => $c->address, 'website' => $c->website, 'tax_number' => $c->tax_id_no, 'balance' => (float) $c->current_balance, 'credit_limit' => (float) $c->credit_limit, 'payment_terms' => $c->payment_terms]; }
    private function present(SalesOrder $order): array { return $order->load(['creator:id,name', 'estimation:id,estimation_no,issue_date'])->toArray() + ['activities' => DB::table('sales_order_activities')->where('sales_order_id', $order->id)->latest('id')->get(), 'attachments' => DB::table('sales_order_attachments')->where('sales_order_id', $order->id)->get(['id', 'name', 'size', 'created_at'])]; }
    private function audit(Request $request, SalesOrder $order, string $action, array $changes = []): void { DB::table('sales_order_activities')->insert(['sales_order_id' => $order->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User', 'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now()]); }
    private function ensureProject(Request $request, SalesOrder $order): void { abort_unless((int) $order->project_id === $this->accountSetupProjectId($request), 404); }
    private function nextNumber(int $projectId): string { $next = (int) SalesOrder::where('project_id', $projectId)->max('id') + 1; do { $number = 'SO-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); } while (SalesOrder::where('project_id', $projectId)->where('order_no', $number)->exists()); return $number; }
}
