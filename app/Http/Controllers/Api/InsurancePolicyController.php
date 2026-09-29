<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsurancePolicy;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsurancePolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = InsurancePolicy::query()
                ->with('vehicle')
                ->where('is_deleted', 0);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($request->filled('coverage_type')) {
                $query->where('coverage_type', $request->coverage_type);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('insurance_code', 'like', "%{$search}%")
                        ->orWhere('insurance_company', 'like', "%{$search}%")
                        ->orWhere('policy_number', 'like', "%{$search}%")
                        ->orWhere('company_phone', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                            $vehicleQuery
                                ->where('vehicle_number', 'like', "%{$search}%")
                                ->orWhere('make_brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        });
                });
            }

            $items = $query
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $items->getCollection()->transform(
                fn (InsurancePolicy $item) => $this->transformInsurance($item)
            );

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Insurance policies retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve insurance policies',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = InsurancePolicy::query()
                ->with('vehicle')
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance policy not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformInsurance($item),
                'message' => 'Insurance policy retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve insurance policy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        return $this->show($id);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateInsurance($request);

            $item = InsurancePolicy::create([
                'project_id' => 1,
                'insurance_code' => $this->generateInsuranceCode(),
                'vehicle_id' => $validated['vehicle_id'],
                'insurance_company' => $validated['insurance_company'],
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'policy_number' => $validated['policy_number'],
                'coverage_type' => $validated['coverage_type'] ?? null,
                'insurance_type' => $validated['insurance_type'] ?? null,
                'agent_name' => $validated['agent_name'] ?? null,
                'agent_phone' => $validated['agent_phone'] ?? null,
                'agent_email' => $validated['agent_email'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'coverage_amount' => $validated['coverage_amount'] ?? null,
                'insured_value' => $validated['insured_value'] ?? null,
                'deductible' => $validated['deductible'] ?? null,
                'currency' => $validated['currency'] ?? 'USD',
                'premium_amount' => $validated['premium_amount'] ?? null,
                'sales_tax' => $validated['sales_tax'] ?? null,
                'total_paid' => $validated['total_paid'] ?? null,
                'payment_frequency' => $validated['payment_frequency'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'charging_date' => $validated['charging_date'] ?? null,
                'policy_data' => $validated['policy_data'] ?? [],
                'reminders' => $validated['reminders'] ?? [],
                'claim_history' => $validated['claim_history'] ?? [],
                'payment_history' => $validated['payment_history'] ?? [],
                'document_name' => $validated['document_name'] ?? null,
                'document_path' => $validated['document_path'] ?? null,
                'calendar_reminder' => $validated['calendar_reminder'] ?? false,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            $item->load('vehicle');

            return response()->json([
                'success' => true,
                'data' => $this->transformInsurance($item),
                'message' => 'Insurance policy created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create insurance policy',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = InsurancePolicy::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance policy not found',
                ], 404);
            }

            $validated = $this->validateInsurance($request, $item->id);

            $item->update([
                'vehicle_id' => $validated['vehicle_id'],
                'insurance_company' => $validated['insurance_company'],
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'policy_number' => $validated['policy_number'],
                'coverage_type' => $validated['coverage_type'] ?? null,
                'insurance_type' => $validated['insurance_type'] ?? null,
                'agent_name' => $validated['agent_name'] ?? null,
                'agent_phone' => $validated['agent_phone'] ?? null,
                'agent_email' => $validated['agent_email'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'coverage_amount' => $validated['coverage_amount'] ?? null,
                'insured_value' => $validated['insured_value'] ?? null,
                'deductible' => $validated['deductible'] ?? null,
                'currency' => $validated['currency'] ?? 'USD',
                'premium_amount' => $validated['premium_amount'] ?? null,
                'sales_tax' => $validated['sales_tax'] ?? null,
                'total_paid' => $validated['total_paid'] ?? null,
                'payment_frequency' => $validated['payment_frequency'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'charging_date' => $validated['charging_date'] ?? null,
                'policy_data' => $validated['policy_data'] ?? [],
                'reminders' => $validated['reminders'] ?? [],
                'claim_history' => $validated['claim_history'] ?? [],
                'payment_history' => $validated['payment_history'] ?? [],
                'document_name' => $validated['document_name'] ?? null,
                'document_path' => $validated['document_path'] ?? null,
                'calendar_reminder' => $validated['calendar_reminder'] ?? false,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            $item->load('vehicle');

            return response()->json([
                'success' => true,
                'data' => $this->transformInsurance($item->fresh('vehicle')),
                'message' => 'Insurance policy updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update insurance policy',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = InsurancePolicy::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance policy not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Insurance policy deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete insurance policy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateInsurance(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'insurance_company' => 'required|string|max:150',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:150',
            'company_address' => 'nullable|string|max:500',
            'policy_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('insurance_policies', 'policy_number')
                    ->ignore($id)
                    ->where(fn ($query) => $query->where('is_deleted', 0)),
            ],
            'coverage_type' => ['nullable', 'string', Rule::in(InsurancePolicy::COVERAGE_TYPES)],
            'insurance_type' => 'nullable|string|max:100',
            'agent_name' => 'nullable|string|max:150',
            'agent_phone' => 'nullable|string|max:50',
            'agent_email' => 'nullable|email|max:150',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'coverage_amount' => 'nullable|numeric|min:0',
            'insured_value' => 'nullable|numeric|min:0',
            'deductible' => 'nullable|numeric|min:0',
            'currency' => ['nullable', 'string', Rule::in(InsurancePolicy::CURRENCIES)],
            'premium_amount' => 'nullable|numeric|min:0',
            'sales_tax' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'payment_frequency' => ['nullable', 'string', Rule::in(InsurancePolicy::PAYMENT_FREQUENCIES)],
            'payment_method' => 'nullable|string|max:80',
            'charging_date' => 'nullable|date',
            'policy_data' => 'nullable|array',
            'reminders' => 'nullable|array',
            'claim_history' => 'nullable|array',
            'payment_history' => 'nullable|array',
            'document_name' => 'nullable|string|max:255',
            'document_path' => 'nullable|string|max:500',
            'calendar_reminder' => 'nullable|boolean',
            'status' => ['nullable', 'integer', Rule::in(array_keys(InsurancePolicy::STATUS))],
            'notes' => 'nullable|string',
        ]);
    }

    protected function generateInsuranceCode(): string
    {
        $year = now()->format('Y');
        $count = InsurancePolicy::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('INS-%s-%03d', $year, $count);
    }

    protected function transformInsurance(InsurancePolicy $item): array
    {
        $vehicle = $item->vehicle;

        return [
            'id' => $item->id,
            'insurance_code' => $item->insurance_code,
            'vehicle_id' => $item->vehicle_id,
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'make_brand' => $vehicle->make_brand,
                'model' => $vehicle->model,
                'label' => trim(sprintf('%s (%s %s)', $vehicle->vehicle_number, $vehicle->make_brand, $vehicle->model)),
            ] : null,
            'vehicle_number' => $vehicle?->vehicle_number,
            'vehicle_make_model' => $vehicle ? trim(sprintf('%s %s', $vehicle->make_brand, $vehicle->model)) : null,
            'insurance_company' => $item->insurance_company,
            'company_phone' => $item->company_phone,
            'company_email' => $item->company_email,
            'company_address' => $item->company_address,
            'policy_number' => $item->policy_number,
            'coverage_type' => $item->coverage_type,
            'insurance_type' => $item->insurance_type,
            'agent_name' => $item->agent_name,
            'agent_phone' => $item->agent_phone,
            'agent_email' => $item->agent_email,
            'start_date' => optional($item->start_date)->format('Y-m-d'),
            'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
            'coverage_amount' => $item->coverage_amount !== null ? (float) $item->coverage_amount : null,
            'insured_value' => $item->insured_value !== null ? (float) $item->insured_value : null,
            'deductible' => $item->deductible !== null ? (float) $item->deductible : null,
            'currency' => $item->currency,
            'premium_amount' => $item->premium_amount !== null ? (float) $item->premium_amount : null,
            'sales_tax' => $item->sales_tax !== null ? (float) $item->sales_tax : null,
            'total_paid' => $item->total_paid !== null ? (float) $item->total_paid : null,
            'payment_frequency' => $item->payment_frequency,
            'payment_method' => $item->payment_method,
            'charging_date' => optional($item->charging_date)->format('Y-m-d'),
            'policy_data' => $item->policy_data ?? [],
            'reminders' => $item->reminders ?? [],
            'claim_history' => $item->claim_history ?? [],
            'payment_history' => $item->payment_history ?? [],
            'document_name' => $item->document_name,
            'document_path' => $item->document_path,
            'calendar_reminder' => (bool) $item->calendar_reminder,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'notes' => $item->notes,
            'created_at' => optional($item->created_at)->toIso8601String(),
            'updated_at' => optional($item->updated_at)->toIso8601String(),
        ];
    }
}
