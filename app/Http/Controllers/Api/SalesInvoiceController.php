<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Http\Controllers\Controller;
use App\Models\AccountHolder;
use App\Models\AccountSetup;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesInvoiceController extends Controller
{
    use ResolvesAccountSetupProject;

    public function options(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $customers = AccountHolder::where('project_id', $projectId)->where('account_type', 1)->where('is_deleted', false)->where('status_active', true)->orderBy('company_name')->get()->map(fn ($customer) => $this->customerDetails($customer));
        $orders = SalesOrder::where('project_id', $projectId)->whereIn('status', ['Confirmed', 'Completed'])->latest('order_date')->get();
        return response()->json(['data' => ['customers' => $customers, 'orders' => $orders]]);
    }

    public function index(Request $request)
    {
        $query = SalesInvoice::with(['salesOrder:id,order_no,order_date,estimation_id', 'creator:id,name'])->where('project_id', $this->accountSetupProjectId($request))->latest('invoice_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('invoice_no', 'like', "%{$term}%")->orWhere('customer_details->name', 'like', "%{$term}%"));
        }
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, SalesInvoice $salesInvoice)
    {
        $this->ensureProject($request, $salesInvoice);
        return response()->json(['data' => $this->present($salesInvoice)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $invoice = DB::transaction(function () use ($request, $projectId, $data) {
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['invoice_no'] = $data['invoice_no'] ?: $this->nextNumber($projectId);
            abort_if(SalesInvoice::where('project_id', $projectId)->where('invoice_no', $data['invoice_no'])->exists(), 422, 'This sales invoice number is already in use.');
            return SalesInvoice::create($data + ['project_id' => $projectId, 'created_by' => $request->user()->id]);
        });
        return response()->json(['data' => $this->present($invoice)], 201);
    }

    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        $this->ensureProject($request, $salesInvoice);
        abort_if((float) $salesInvoice->paid > 0, 422, 'An invoice with payments cannot be edited.');
        $data = $this->validated($request, $salesInvoice->project_id, $salesInvoice);
        $salesInvoice->update($data);
        return response()->json(['data' => $this->present($salesInvoice->fresh())]);
    }

    public function destroy(Request $request, SalesInvoice $salesInvoice)
    {
        $this->ensureProject($request, $salesInvoice);
        abort_if((float) $salesInvoice->paid > 0, 422, 'An invoice with payments cannot be deleted.');
        $salesInvoice->delete();
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, SalesInvoice $salesInvoice)
    {
        $this->ensureProject($request, $salesInvoice);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject'])['action'];
        $allowed = ['submit' => ['Draft', 'Rejected'], 'approve' => ['Pending'], 'reject' => ['Pending']][$action];
        abort_unless(in_array($salesInvoice->status, $allowed, true), 422, 'This action is no longer available.');
        $salesInvoice->update(['status' => ['submit' => 'Pending', 'approve' => 'Open', 'reject' => 'Rejected'][$action]]);
        return response()->json(['data' => $this->present($salesInvoice)]);
    }

    public function pay(Request $request, SalesInvoice $salesInvoice)
    {
        $this->ensureProject($request, $salesInvoice);
        abort_unless($salesInvoice->status === 'Open', 422, 'Only open invoices can receive payments.');
        $data = $request->validate(['payment_no' => 'required|string|max:100', 'payment_date' => 'required|date', 'amount' => 'required|numeric|gt:0', 'method' => 'nullable|string|max:100', 'reference' => 'nullable|string|max:100']);
        DB::transaction(function () use ($salesInvoice, $data) {
            $invoice = SalesInvoice::whereKey($salesInvoice->id)->lockForUpdate()->firstOrFail();
            abort_if((float) $data['amount'] > round((float) $invoice->total - (float) $invoice->paid, 2), 422, 'Payment exceeds the balance due.');
            $invoice->payments()->create($data);
            $invoice->paid = round((float) $invoice->paid + (float) $data['amount'], 2);
            $invoice->payment_status = $invoice->paid >= $invoice->total ? 'Paid' : 'Partial';
            $invoice->save();
        });
        return response()->json(['data' => $this->present($salesInvoice->fresh())]);
    }

    private function validated(Request $request, int $projectId, ?SalesInvoice $invoice = null): array
    {
        $data = $request->validate([
            'invoice_no' => [$invoice ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('sales_invoices')->where('project_id', $projectId)->ignore($invoice?->id)],
            'customer_id' => ['required', 'integer', Rule::exists('account_holders', 'id')->where(fn ($q) => $q->where('project_id', $projectId)->where('account_type', 1))],
            'sales_order_id' => ['nullable', 'integer', Rule::exists('sales_orders', 'id')->where('project_id', $projectId)],
            'invoice_date' => 'required|date', 'due_date' => 'nullable|date|after_or_equal:invoice_date', 'delivery_date' => 'nullable|date',
            'customer_po_no' => 'nullable|string|max:100', 'salesperson' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255',
            'currency_code' => 'required|string|size:3', 'exchange_rate' => 'required|numeric|gt:0',
            'price_list' => 'nullable|string|max:255', 'sales_channel' => 'nullable|string|max:100',
            'delivery_location' => 'nullable|string|max:255', 'shipping_method' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100', 'shipping_terms' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:100', 'reference' => 'nullable|string|max:255',
            'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
            'payment_information' => 'nullable|string|max:5000',
            'job_order_no' => 'nullable|string|max:100', 'job_order_date' => 'nullable|date',
            'job_order_status' => 'nullable|string|max:100', 'job_site_name' => 'nullable|string|max:255',
            'job_site_address' => 'nullable|string|max:255', 'tax_registration_no' => 'nullable|string|max:100',
            'approved_by' => 'nullable|string|max:255', 'internal_notes' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1|max:500',
            'items.*.name' => 'required|string|max:255', 'items.*.description' => 'nullable|string|max:1000',
            'items.*.uom' => 'nullable|string|max:40', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000',
            'items.*.unit_price' => 'required|numeric|min:0|max:1000000',
            'items.*.discount' => 'required|numeric|min:0|max:100',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);
        $data['invoice_no'] ??= '';
        if (!empty($data['sales_order_id'])) {
            $order = SalesOrder::where('project_id', $projectId)->findOrFail($data['sales_order_id']);
            abort_unless((int) $order->customer_id === (int) $data['customer_id'], 422, 'The sales order belongs to a different customer.');
        }
        $customer = AccountHolder::where('project_id', $projectId)->findOrFail($data['customer_id']);
        $data['customer_details'] = $this->customerDetails($customer);
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price']), 2);
        $data['discount'] = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * $item['discount'] / 100), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * (1 - $item['discount'] / 100) * $item['tax_rate'] / 100), 2);
        $data['total'] = round($data['subtotal'] - $data['discount'] + $data['sales_tax'], 2);
        return $data;
    }

    private function customerDetails(AccountHolder $customer): array
    {
        return ['id' => $customer->id, 'customer_no' => $customer->system_no, 'name' => $customer->company_name ?: $customer->account_name, 'contact_person' => $customer->primary_contact_name ?: $customer->account_name, 'phone' => $customer->cell_phone ?: $customer->office_phone, 'email' => $customer->email, 'address' => $customer->address, 'website' => $customer->website, 'balance' => (float) $customer->current_balance, 'credit_limit' => (float) $customer->credit_limit];
    }

    private function present(SalesInvoice $invoice): array
    {
        return $invoice->load(['salesOrder.estimation:id,estimation_no,issue_date', 'creator:id,name', 'payments'])->toArray();
    }

    private function ensureProject(Request $request, SalesInvoice $invoice): void
    {
        abort_unless((int) $invoice->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) SalesInvoice::where('project_id', $projectId)->max('id') + 1;
        do { $number = 'SINV-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT); }
        while (SalesInvoice::where('project_id', $projectId)->where('invoice_no', $number)->exists());
        return $number;
    }
}
