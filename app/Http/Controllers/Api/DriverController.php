<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Driver::query()->where('is_deleted', 0);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('driver_code', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%");
                });
            }

            $items = $query
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $items->getCollection()->transform(
                fn (Driver $driver) => $this->transformDriver($driver)
            );

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Drivers retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve drivers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $driver = Driver::query()
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformDriver($driver),
                'message' => 'Driver retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve driver',
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
            $validated = $this->validateDriver($request);

            $driver = Driver::create([
                'project_id' => 1,
                'driver_code' => $this->generateDriverCode(),
                'driver_name' => $validated['driver_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                'license_number' => $validated['license_number'],
                'license_expiry_date' => $validated['license_expiry_date'] ?? null,
                'license_class' => $validated['license_class'] ?? null,
                'assigned_vehicle_ids' => $validated['assigned_vehicle_ids'] ?? [],
                'hire_date' => $validated['hire_date'] ?? null,
                'employment_type' => $validated['employment_type'] ?? null,
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'profile_data' => $validated['profile_data'] ?? [],
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformDriver($driver),
                'message' => 'Driver created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create driver',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $driver = Driver::find($id);

            if (! $driver || $driver->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                ], 404);
            }

            $validated = $this->validateDriver($request, $driver->id);

            $driver->update([
                'driver_name' => $validated['driver_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                'license_number' => $validated['license_number'],
                'license_expiry_date' => $validated['license_expiry_date'] ?? null,
                'license_class' => $validated['license_class'] ?? null,
                'assigned_vehicle_ids' => $validated['assigned_vehicle_ids'] ?? [],
                'hire_date' => $validated['hire_date'] ?? null,
                'employment_type' => $validated['employment_type'] ?? null,
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'profile_data' => $validated['profile_data'] ?? [],
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformDriver($driver->fresh()),
                'message' => 'Driver updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update driver',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $driver = Driver::find($id);

            if (! $driver || $driver->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                ], 404);
            }

            $driver->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Driver deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateDriver(Request $request, ?int $driverId = null): array
    {
        return $request->validate([
            'driver_name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_number' => 'nullable|string|max:50',
            'license_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('drivers', 'license_number')
                    ->ignore($driverId)
                    ->where(fn ($query) => $query->where('is_deleted', 0)),
            ],
            'license_expiry_date' => 'nullable|date',
            'license_class' => ['nullable', 'string', Rule::in(Driver::LICENSE_CLASSES)],
            'assigned_vehicle_ids' => 'nullable|array',
            'assigned_vehicle_ids.*' => 'integer|exists:vehicles,id',
            'hire_date' => 'nullable|date',
            'employment_type' => ['nullable', 'string', Rule::in(Driver::EMPLOYMENT_TYPES)],
            'hourly_rate' => 'nullable|numeric|min:0',
            'profile_data' => 'nullable|array',
            'status' => ['nullable', 'integer', Rule::in(array_keys(Driver::STATUS))],
            'notes' => 'nullable|string',
        ]);
    }

    protected function generateDriverCode(): string
    {
        $year = now()->format('Y');
        $count = Driver::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('DRV-%s-%03d', $year, $count);
    }

    protected function transformDriver(Driver $driver): array
    {
        $assignedVehicleIds = collect($driver->assigned_vehicle_ids ?: [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $vehicles = Vehicle::query()
            ->whereIn('id', $assignedVehicleIds)
            ->get(['id', 'vehicle_number', 'make_brand', 'model']);

        $vehicleMap = $vehicles->keyBy('id');
        $assignedVehicles = $assignedVehicleIds
            ->map(function (int $id) use ($vehicleMap) {
                $vehicle = $vehicleMap->get($id);
                if (! $vehicle) {
                    return null;
                }

                return [
                    'id' => $vehicle->id,
                    'vehicle_number' => $vehicle->vehicle_number,
                    'label' => trim(sprintf(
                        '%s (%s %s)',
                        $vehicle->vehicle_number,
                        $vehicle->make_brand,
                        $vehicle->model
                    )),
                ];
            })
            ->filter()
            ->values();

        return [
            'id' => $driver->id,
            'driver_code' => $driver->driver_code,
            'driver_name' => $driver->driver_name,
            'contact_number' => $driver->contact_number,
            'email' => $driver->email,
            'date_of_birth' => optional($driver->date_of_birth)->format('Y-m-d'),
            'address' => $driver->address,
            'emergency_contact_name' => $driver->emergency_contact_name,
            'emergency_contact_number' => $driver->emergency_contact_number,
            'license_number' => $driver->license_number,
            'license_expiry_date' => optional($driver->license_expiry_date)->format('Y-m-d'),
            'license_class' => $driver->license_class,
            'assigned_vehicle_ids' => $assignedVehicleIds->all(),
            'assigned_vehicles' => $assignedVehicles->all(),
            'assigned_vehicle_summary' => $assignedVehicles->pluck('label')->implode(', '),
            'hire_date' => optional($driver->hire_date)->format('Y-m-d'),
            'employment_type' => $driver->employment_type,
            'hourly_rate' => $driver->hourly_rate !== null ? (float) $driver->hourly_rate : null,
            'profile_data' => $driver->profile_data ?? [],
            'status' => $driver->status,
            'status_label' => $driver->status_label,
            'notes' => $driver->notes,
            'created_at' => optional($driver->created_at)->toIso8601String(),
            'updated_at' => optional($driver->updated_at)->toIso8601String(),
        ];
    }
}
