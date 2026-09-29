<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = InsuranceCompany::query()->with('country:id,country_name,iso_code')
            ->where('project_id', $request->user()->project_id);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(fn ($q) => $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('company_no', 'like', "%{$search}%")
                ->orWhere('primary_contact_name', 'like', "%{$search}%")
                ->orWhere('agent_broker_name', 'like', "%{$search}%"));
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function options()
    {
        return response()->json(['success' => true, 'data' => [
            'countries' => Country::query()->orderBy('country_name')->get(['id', 'country_name', 'iso_code']),
            'company_types' => ['Insurance Carrier', 'Insurance Agency', 'Insurance Broker', 'Third-Party Administrator'],
            'insurance_types' => ['Property Insurance', 'Vehicle Insurance', 'Other Insurance'],
        ]]);
    }

    public function store(Request $request)
    {
        $projectId = $request->user()->project_id;
        $company = DB::transaction(fn () => InsuranceCompany::create($this->validated($request) + [
            'project_id' => $projectId,
            'company_no' => $this->nextCompanyNo($projectId),
            'created_by' => $request->user()->id,
        ]));

        return response()->json(['success' => true, 'data' => $company], 201);
    }

    public function show(Request $request, InsuranceCompany $insuranceCompany)
    {
        $this->authorizeCompany($request, $insuranceCompany);
        return response()->json(['success' => true, 'data' => $insuranceCompany->load('country:id,country_name,iso_code')]);
    }

    public function update(Request $request, InsuranceCompany $insuranceCompany)
    {
        $this->authorizeCompany($request, $insuranceCompany);
        $insuranceCompany->update($this->validated($request) + ['updated_by' => $request->user()->id]);
        return response()->json(['success' => true, 'data' => $insuranceCompany->fresh()->load('country:id,country_name,iso_code')]);
    }

    public function destroy(Request $request, InsuranceCompany $insuranceCompany)
    {
        $this->authorizeCompany($request, $insuranceCompany);
        $insuranceCompany->delete();
        return response()->json(['success' => true]);
    }

    private function authorizeCompany(Request $request, InsuranceCompany $company): void
    {
        abort_unless((int) $company->project_id === (int) $request->user()->project_id, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'], 'company_type' => ['required', 'string', 'max:100'],
            'insurance_type' => ['required', 'string', 'max:100'], 'status_active' => ['required', 'boolean'],
            'policy_no' => ['nullable', 'string', 'max:80'], 'policy_status' => ['required', 'in:Active,Pending,Inactive'],
            'balance' => ['nullable', 'numeric', 'min:0'], 'expiry_date' => ['nullable', 'date'],
            'agent_broker_name' => ['nullable', 'string', 'max:255'], 'agent_broker_phone' => ['nullable', 'string', 'max:50'],
            'agent_broker_email' => ['nullable', 'email', 'max:255'], 'primary_contact_name' => ['required', 'string', 'max:255'],
            'primary_contact_phone' => ['required', 'string', 'max:50'], 'primary_contact_email' => ['nullable', 'email', 'max:255'],
            'street_address' => ['required', 'string', 'max:255'], 'city' => ['required', 'string', 'max:100'],
            'state_province' => ['nullable', 'string', 'max:100'], 'postal_code' => ['required', 'string', 'max:30'],
            'country_id' => ['required', 'integer', 'exists:countries,id'], 'notes' => ['nullable', 'string'],
        ]);
    }

    private function nextCompanyNo(?int $projectId): string
    {
        $last = InsuranceCompany::query()->where('project_id', $projectId)->where('company_no', 'like', 'IC-%')
            ->lockForUpdate()->orderByDesc('id')->value('company_no');
        return 'IC-'.str_pad((string) ($last ? (int) substr($last, 3) + 1 : 1), 3, '0', STR_PAD_LEFT);
    }
}
