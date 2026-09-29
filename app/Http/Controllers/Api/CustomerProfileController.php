<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerProfile::query()->where('project_id', $request->user()->project_id)->where('is_deleted', false);
        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(fn ($q) => $q->where('account_name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%")->orWhere('system_no', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($request->filled('status')) $query->where('status_active', $request->boolean('status'));
        if ($request->filled('customer_type')) $query->where('customer_type', $request->string('customer_type')->trim());

        $customers = $query->latest()->get()->map(fn (CustomerProfile $customer) => $this->present($customer));
        return response()->json(['success' => true, 'data' => $customers, 'summary' => $this->summary($customers)]);
    }

    public function show(Request $request, CustomerProfile $customer)
    {
        $this->authorizeCustomer($request, $customer);
        return response()->json(['success' => true, 'data' => $this->present($customer)]);
    }

    public function store(Request $request)
    {
        $projectId = $request->user()->project_id;
        $customer = DB::transaction(function () use ($request, $projectId) {
            $data = $this->validated($request);
            return CustomerProfile::create($data + ['project_id' => $projectId, 'system_prefix' => 'CUS', 'system_no' => $this->nextCustomerNo($projectId), 'inserted_by' => $request->user()->id, 'updated_by' => $request->user()->id, 'is_deleted' => false]);
        });
        return response()->json(['success' => true, 'data' => $this->present($customer), 'message' => 'Customer created.'], 201);
    }

    public function update(Request $request, CustomerProfile $customer)
    {
        $this->authorizeCustomer($request, $customer);
        $customer->update($this->validated($request) + ['updated_by' => $request->user()->id]);
        return response()->json(['success' => true, 'data' => $this->present($customer->fresh()), 'message' => 'Customer updated.']);
    }

    public function destroy(Request $request, CustomerProfile $customer)
    {
        $this->authorizeCustomer($request, $customer);
        $customer->update(['is_deleted' => true, 'updated_by' => $request->user()->id]);
        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'account_name' => ['required', 'string', 'max:255'], 'company_name' => ['nullable', 'string', 'max:255'], 'customer_type' => ['required', 'in:Residential,Commercial'],
            'cell_phone' => ['nullable', 'string', 'max:50'], 'office_phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'], 'status_active' => ['required', 'boolean'],
            'total_invoices' => ['nullable', 'integer', 'min:0'], 'total_outstanding' => ['nullable', 'numeric', 'min:0'], 'current_balance' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function nextCustomerNo($projectId): string
    {
        $last = CustomerProfile::where('project_id', $projectId)->lockForUpdate()->latest('id')->value('system_no');
        return 'CUS-'.str_pad((string) (($last ? (int) preg_replace('/\D/', '', $last) : 0) + 1), 6, '0', STR_PAD_LEFT);
    }

    private function authorizeCustomer(Request $request, CustomerProfile $customer): void { abort_unless((int) $customer->project_id === (int) $request->user()->project_id, 404); }
    private function present(CustomerProfile $customer): array { return ['id' => $customer->id, 'customer_id' => $customer->system_no, 'name' => $customer->company_name ?: $customer->account_name, 'account_name' => $customer->account_name, 'phone' => $customer->cell_phone ?: $customer->office_phone, 'email' => $customer->email, 'customer_type' => $customer->customer_type ?: 'Residential', 'total_invoices' => $customer->total_invoices, 'total_outstanding' => $customer->total_outstanding, 'current_balance' => $customer->current_balance, 'status_active' => $customer->status_active]; }
    private function summary($customers): array { return ['total_customers' => $customers->count(), 'active_customers' => $customers->where('status_active', true)->count(), 'inactive_customers' => $customers->where('status_active', false)->count(), 'total_outstanding' => $customers->sum(fn ($c) => (float) $c['total_outstanding'])]; }
}
