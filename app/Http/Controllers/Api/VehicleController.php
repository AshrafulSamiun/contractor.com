<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $baseQuery = Vehicle::query()->where('is_deleted', 0);
            $query = $this->applyFilters(clone $baseQuery, $request);

            $items = $query
                ->orderBy('vehicle_number')
                ->paginate($request->integer('per_page', 15));

            $items->getCollection()->transform(fn (Vehicle $vehicle) => $this->transformVehicle($vehicle));

            return response()->json([
                'success' => true,
                'data' => $items,
                'summary' => [
                    'total_vehicles' => (clone $baseQuery)->count(),
                    'in_service' => (clone $baseQuery)->where('status', 1)->count(),
                    'maintenance' => (clone $baseQuery)->where('status', 3)->count(),
                    'out_of_service' => (clone $baseQuery)->where('status', 4)->count(),
                    'expiring_documents' => (clone $baseQuery)
                        ->where(function ($documentQuery) {
                            $documentQuery
                                ->whereBetween('plate_expiry_date', [today(), today()->addDays(30)])
                                ->orWhereBetween('insurance_expiry_date', [today(), today()->addDays(30)]);
                        })
                        ->count(),
                ],
                'message' => 'Vehicles retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve vehicles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::query()
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformVehicle($vehicle),
                'message' => 'Vehicle retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve vehicle',
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
            $validated = $this->validateVehicle($request);

            $vehicle = Vehicle::create($this->buildVehiclePayload($validated, true) + [
                'project_id' => 1,
                'vehicle_code' => $this->generateVehicleCode(),
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformVehicle($vehicle),
                'message' => 'Vehicle created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vehicle',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::find($id);

            if (! $vehicle || $vehicle->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle not found',
                ], 404);
            }

            $validated = $this->validateVehicle($request, $vehicle->id);

            $vehicle->update($this->buildVehiclePayload($validated) + [
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformVehicle($vehicle->fresh()),
                'message' => 'Vehicle updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::find($id);

            if (! $vehicle || $vehicle->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle not found',
                ], 404);
            }

            $vehicle->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vehicle',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }

        if ($request->filled('make_brand')) {
            $query->where('make_brand', trim((string) $request->make_brand));
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', trim((string) $request->fuel_type));
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', trim((string) $request->vehicle_type));
        }

        if ($request->filled('vehicle_year')) {
            $query->where('vehicle_year', (int) $request->vehicle_year);
        }

        if ($request->filled('in_service')) {
            $query->where('in_service', filter_var($request->in_service, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $request->integer('in_service'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('vehicle_code', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('make_brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('vehicle_type', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('fuel_type', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('assigned_driver', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    protected function validateVehicle(Request $request, ?int $vehicleId = null): array
    {
        return $request->validate([
            'vehicle_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vehicles', 'vehicle_number')->ignore($vehicleId)->where(fn ($query) => $query->where('is_deleted', 0)),
            ],
            'make_brand' => 'required|string|max:150',
            'model' => 'required|string|max:150',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:2100',
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:100',
            'fuel_type' => ['nullable', 'string', 'max:50', Rule::in(Vehicle::FUEL_TYPES)],
            'vehicle_keys_tag_no' => 'nullable|string|max:100',
            'plate_number' => 'nullable|string|max:100',
            'plate_expiry_date' => 'nullable|date',
            'assignment_start_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'sales_tax' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'number_of_installments' => 'nullable|integer|min:0|max:60',
            'first_installment_amount' => 'nullable|numeric|min:0',
            'first_installment_date' => 'nullable|date',
            'last_installment_amount' => 'nullable|numeric|min:0',
            'last_installment_date' => 'nullable|date',
            'current_mileage' => 'nullable|integer|min:0',
            'insurance_provider' => 'nullable|string|max:150',
            'policy_number' => 'nullable|string|max:100',
            'insurance_start_date' => 'nullable|date',
            'insurance_expiry_date' => 'nullable|date',
            'insurance_expired' => 'nullable|boolean',
            'assigned_driver' => 'nullable|string|max:150',
            'in_service' => 'nullable|boolean',
            'seller_name' => 'nullable|string|max:150',
            'seller_company_name' => 'nullable|string|max:150',
            'seller_phone' => 'nullable|string|max:50',
            'seller_email' => 'nullable|email|max:150',
            'seller_website' => 'nullable|string|max:255',
            'car_photos' => 'nullable|array',
            'car_photos.*' => 'nullable|string|max:255',
            'driver_profiles' => 'nullable|array',
            'driver_profiles.*.name' => 'nullable|string|max:150',
            'driver_profiles.*.phone' => 'nullable|string|max:50',
            'driver_profiles.*.email' => 'nullable|email|max:150',
            'driver_profiles.*.address' => 'nullable|string|max:255',
            'driver_profiles.*.license_number' => 'nullable|string|max:100',
            'driver_profiles.*.license_expiry_date' => 'nullable|date',
            'driver_profiles.*.license_expired' => 'nullable|boolean',
            'insurance_documents' => 'nullable|array',
            'insurance_documents.*.insurance_company' => 'nullable|string|max:150',
            'insurance_documents.*.policy_number' => 'nullable|string|max:100',
            'insurance_documents.*.policy_start_date' => 'nullable|date',
            'insurance_documents.*.policy_expiry_date' => 'nullable|date',
            'insurance_documents.*.expired' => 'nullable|boolean',
            'insurance_documents.*.document_name' => 'nullable|string|max:255',
            'safety_equipments' => 'nullable|array',
            'safety_equipments.*.equipment' => 'nullable|string|max:150',
            'safety_equipments.*.available' => 'nullable|boolean',
            'safety_equipments.*.condition' => 'nullable|string|max:100',
            'safety_equipments.*.notes' => 'nullable|string|max:255',
            'status' => ['nullable', 'integer', Rule::in(array_keys(Vehicle::STATUS))],
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }

    protected function generateVehicleCode(): string
    {
        $year = now()->format('Y');
        $count = Vehicle::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('VEH-%s-%03d', $year, $count);
    }

    protected function transformVehicle(Vehicle $vehicle): array
    {
        $primaryInsuranceDocument = $vehicle->primary_insurance_document;

        return [
            'id' => $vehicle->id,
            'vehicle_code' => $vehicle->vehicle_code,
            'vehicle_number' => $vehicle->vehicle_number,
            'make_brand' => $vehicle->make_brand,
            'model' => $vehicle->model,
            'make_model' => trim(sprintf('%s %s', $vehicle->make_brand, $vehicle->model)),
            'vehicle_type' => $vehicle->vehicle_type,
            'vehicle_year' => $vehicle->vehicle_year,
            'color' => $vehicle->color,
            'vin' => $vehicle->vin,
            'fuel_type' => $vehicle->fuel_type,
            'vehicle_keys_tag_no' => $vehicle->vehicle_keys_tag_no,
            'plate_number' => $vehicle->plate_number,
            'plate_expiry_date' => optional($vehicle->plate_expiry_date)->format('Y-m-d'),
            'assignment_start_date' => optional($vehicle->assignment_start_date)->format('Y-m-d'),
            'purchase_date' => optional($vehicle->purchase_date)->format('Y-m-d'),
            'purchase_price' => $vehicle->purchase_price !== null ? (float) $vehicle->purchase_price : null,
            'purchase_invoice_number' => $vehicle->purchase_invoice_number,
            'invoice_date' => optional($vehicle->invoice_date)->format('Y-m-d'),
            'sales_tax' => $vehicle->sales_tax !== null ? (float) $vehicle->sales_tax : null,
            'subtotal' => $vehicle->subtotal !== null ? (float) $vehicle->subtotal : null,
            'total_paid' => $vehicle->total_paid !== null ? (float) $vehicle->total_paid : null,
            'number_of_installments' => $vehicle->number_of_installments,
            'first_installment_amount' => $vehicle->first_installment_amount !== null ? (float) $vehicle->first_installment_amount : null,
            'first_installment_date' => optional($vehicle->first_installment_date)->format('Y-m-d'),
            'last_installment_amount' => $vehicle->last_installment_amount !== null ? (float) $vehicle->last_installment_amount : null,
            'last_installment_date' => optional($vehicle->last_installment_date)->format('Y-m-d'),
            'current_mileage' => $vehicle->current_mileage,
            'insurance_provider' => $vehicle->insurance_provider,
            'policy_number' => $vehicle->policy_number,
            'insurance_start_date' => optional($vehicle->insurance_start_date)->format('Y-m-d'),
            'insurance_expiry_date' => optional($vehicle->insurance_expiry_date)->format('Y-m-d'),
            'insurance_expired' => (bool) $vehicle->insurance_expired,
            'assigned_driver' => $vehicle->assigned_driver,
            'driver_name' => $vehicle->driver_name,
            'driver_initials' => $vehicle->driver_initials,
            'in_service' => (bool) $vehicle->in_service,
            'seller_name' => $vehicle->seller_name,
            'seller_company_name' => $vehicle->seller_company_name,
            'seller_phone' => $vehicle->seller_phone,
            'seller_email' => $vehicle->seller_email,
            'seller_website' => $vehicle->seller_website,
            'car_photos' => $vehicle->car_photos ?? [],
            'primary_photo' => ($vehicle->car_photos ?? [])[0] ?? null,
            'driver_profiles' => $vehicle->driver_profiles ?? [],
            'insurance_documents' => $vehicle->insurance_documents ?? [],
            'primary_insurance_document' => $primaryInsuranceDocument,
            'safety_equipments' => $vehicle->safety_equipments ?? [],
            'status' => $vehicle->status,
            'status_label' => $vehicle->status_label,
            'last_maintenance_date' => optional($vehicle->last_maintenance_date)->format('Y-m-d'),
            'next_maintenance_date' => optional($vehicle->next_maintenance_date)->format('Y-m-d'),
            'notes' => $vehicle->notes,
            'balance_due' => $vehicle->subtotal !== null && $vehicle->total_paid !== null
                ? (float) $vehicle->subtotal - (float) $vehicle->total_paid
                : null,
            'created_at' => optional($vehicle->created_at)->toIso8601String(),
            'updated_at' => optional($vehicle->updated_at)->toIso8601String(),
        ];
    }

    protected function buildVehiclePayload(array $validated, bool $creating = false): array
    {
        $driverProfiles = collect($validated['driver_profiles'] ?? [])
            ->map(fn ($item) => [
                'name' => $item['name'] ?? null,
                'phone' => $item['phone'] ?? null,
                'email' => $item['email'] ?? null,
                'address' => $item['address'] ?? null,
                'license_number' => $item['license_number'] ?? null,
                'license_expiry_date' => $item['license_expiry_date'] ?? null,
                'license_expired' => (bool) ($item['license_expired'] ?? false),
            ])
            ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();

        $insuranceDocuments = collect($validated['insurance_documents'] ?? [])
            ->map(fn ($item) => [
                'insurance_company' => $item['insurance_company'] ?? null,
                'policy_number' => $item['policy_number'] ?? null,
                'policy_start_date' => $item['policy_start_date'] ?? null,
                'policy_expiry_date' => $item['policy_expiry_date'] ?? null,
                'expired' => (bool) ($item['expired'] ?? false),
                'document_name' => $item['document_name'] ?? null,
            ])
            ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();

        $safetyEquipments = collect($validated['safety_equipments'] ?? [])
            ->map(fn ($item) => [
                'equipment' => $item['equipment'] ?? null,
                'available' => (bool) ($item['available'] ?? false),
                'condition' => $item['condition'] ?? null,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn ($item) => ! empty($item['equipment']))
            ->values()
            ->all();

        $carPhotos = collect($validated['car_photos'] ?? [])
            ->filter(fn ($item) => filled($item))
            ->values()
            ->all();

        $assignedDriver = $validated['assigned_driver'] ?? ($driverProfiles[0]['name'] ?? null);
        $primaryInsuranceDocument = $insuranceDocuments[0] ?? [];
        $status = (int) ($validated['status'] ?? ($creating ? 2 : 2));
        $inService = $status === 1;

        return [
            'vehicle_number' => $validated['vehicle_number'],
            'make_brand' => $validated['make_brand'],
            'model' => $validated['model'],
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_year' => $validated['vehicle_year'] ?? null,
            'color' => $validated['color'] ?? null,
            'vin' => $validated['vin'] ?? null,
            'fuel_type' => $validated['fuel_type'] ?? null,
            'vehicle_keys_tag_no' => $validated['vehicle_keys_tag_no'] ?? null,
            'plate_number' => $validated['plate_number'] ?? null,
            'plate_expiry_date' => $validated['plate_expiry_date'] ?? null,
            'assignment_start_date' => $validated['assignment_start_date'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'purchase_invoice_number' => $validated['purchase_invoice_number'] ?? null,
            'invoice_date' => $validated['invoice_date'] ?? null,
            'sales_tax' => $validated['sales_tax'] ?? null,
            'subtotal' => $validated['subtotal'] ?? null,
            'total_paid' => $validated['total_paid'] ?? null,
            'number_of_installments' => $validated['number_of_installments'] ?? null,
            'first_installment_amount' => $validated['first_installment_amount'] ?? null,
            'first_installment_date' => $validated['first_installment_date'] ?? null,
            'last_installment_amount' => $validated['last_installment_amount'] ?? null,
            'last_installment_date' => $validated['last_installment_date'] ?? ($validated['first_installment_date'] ?? null),
            'current_mileage' => $validated['current_mileage'] ?? null,
            'insurance_provider' => $validated['insurance_provider'] ?? ($primaryInsuranceDocument['insurance_company'] ?? null),
            'policy_number' => $validated['policy_number'] ?? ($primaryInsuranceDocument['policy_number'] ?? null),
            'insurance_start_date' => $validated['insurance_start_date'] ?? ($primaryInsuranceDocument['policy_start_date'] ?? null),
            'insurance_expiry_date' => $validated['insurance_expiry_date'] ?? ($primaryInsuranceDocument['policy_expiry_date'] ?? null),
            'insurance_expired' => (bool) ($validated['insurance_expired'] ?? false),
            'assigned_driver' => $assignedDriver,
            'in_service' => (bool) ($validated['in_service'] ?? $inService),
            'seller_name' => $validated['seller_name'] ?? null,
            'seller_company_name' => $validated['seller_company_name'] ?? null,
            'seller_phone' => $validated['seller_phone'] ?? null,
            'seller_email' => $validated['seller_email'] ?? null,
            'seller_website' => $validated['seller_website'] ?? null,
            'car_photos' => $carPhotos,
            'driver_profiles' => $driverProfiles,
            'insurance_documents' => $insuranceDocuments,
            'safety_equipments' => $safetyEquipments,
            'status' => $status,
            'last_maintenance_date' => $validated['last_maintenance_date'] ?? null,
            'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }
}
