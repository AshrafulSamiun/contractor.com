<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DailyLog::query()
                ->with(['vehicle', 'driver'])
                ->where('is_deleted', 0);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($request->filled('log_date')) {
                $query->whereDate('log_date', $request->log_date);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('log_code', 'like', "%{$search}%")
                        ->orWhere('purpose_location', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                            $vehicleQuery
                                ->where('vehicle_number', 'like', "%{$search}%")
                                ->orWhere('make_brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        })
                        ->orWhereHas('driver', function ($driverQuery) use ($search) {
                            $driverQuery->where('driver_name', 'like', "%{$search}%");
                        });
                });
            }

            $items = $query
                ->orderByDesc('log_date')
                ->orderBy('start_time')
                ->paginate($request->integer('per_page', 30));

            $items->getCollection()->transform(
                fn (DailyLog $item) => $this->transformLog($item)
            );

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Daily logs retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = DailyLog::query()
                ->with(['vehicle', 'driver'])
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily log not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($item),
                'message' => 'Daily log retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily log',
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
            $validated = $this->validateLog($request);

            $item = DailyLog::create([
                'project_id' => 1,
                'log_code' => $this->generateLogCode(),
                'log_date' => $validated['log_date'],
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'] ?? null,
                'start_odometer' => $validated['start_odometer'],
                'end_odometer' => $validated['end_odometer'] ?? null,
                'miles_driven' => $this->calculateMiles($validated),
                'fuel_added' => $validated['fuel_added'] ?? null,
                'purpose_location' => $validated['purpose_location'] ?? null,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($item),
                'message' => 'Daily log created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create daily log',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = DailyLog::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily log not found',
                ], 404);
            }

            $validated = $this->validateLog($request);

            $item->update([
                'log_date' => $validated['log_date'],
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'] ?? null,
                'start_odometer' => $validated['start_odometer'],
                'end_odometer' => $validated['end_odometer'] ?? null,
                'miles_driven' => $this->calculateMiles($validated),
                'fuel_added' => $validated['fuel_added'] ?? null,
                'purpose_location' => $validated['purpose_location'] ?? null,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($item->fresh(['vehicle', 'driver'])),
                'message' => 'Daily log updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update daily log',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = DailyLog::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily log not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Daily log deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete daily log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateLog(Request $request): array
    {
        return $request->validate([
            'log_date' => 'required|date',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'driver_id' => 'required|integer|exists:drivers,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'start_odometer' => 'required|integer|min:0',
            'end_odometer' => 'nullable|integer|gte:start_odometer',
            'fuel_added' => 'nullable|numeric|min:0',
            'purpose_location' => 'nullable|string|max:255',
            'status' => ['nullable', 'integer', Rule::in(array_keys(DailyLog::STATUS))],
            'notes' => 'nullable|string',
        ]);
    }

    protected function generateLogCode(): string
    {
        $count = DailyLog::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('DL-%s-%03d', now()->format('Y'), $count);
    }

    protected function calculateMiles(array $validated): ?int
    {
        if (! isset($validated['end_odometer'])) {
            return null;
        }

        return (int) $validated['end_odometer'] - (int) $validated['start_odometer'];
    }

    protected function transformLog(DailyLog $item): array
    {
        $vehicle = $item->vehicle;
        $driver = $item->driver;

        return [
            'id' => $item->id,
            'log_code' => $item->log_code,
            'log_date' => optional($item->log_date)->format('Y-m-d'),
            'vehicle_id' => $item->vehicle_id,
            'vehicle_number' => $vehicle?->vehicle_number,
            'vehicle_make_model' => $vehicle ? trim(sprintf('%s %s', $vehicle->make_brand, $vehicle->model)) : null,
            'driver_id' => $item->driver_id,
            'driver_name' => $driver?->driver_name,
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'start_odometer' => $item->start_odometer,
            'end_odometer' => $item->end_odometer,
            'miles_driven' => $item->miles_driven,
            'fuel_added' => $item->fuel_added !== null ? (float) $item->fuel_added : null,
            'purpose_location' => $item->purpose_location,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'notes' => $item->notes,
            'created_at' => optional($item->created_at)->toIso8601String(),
            'updated_at' => optional($item->updated_at)->toIso8601String(),
        ];
    }
}
