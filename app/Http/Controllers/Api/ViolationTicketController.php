<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ViolationTicket;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViolationTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ViolationTicket::query()
                ->with(['vehicle', 'driver'])
                ->where('is_deleted', 0);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($request->filled('violation_type')) {
                $query->where('violation_type', $request->violation_type);
            }

            if ($request->filled('driver_id')) {
                $query->where('driver_id', (int) $request->driver_id);
            }

            if ($request->filled('vehicle_id')) {
                $query->where('vehicle_id', (int) $request->vehicle_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('issue_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('issue_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('ticket_code', 'like', "%{$search}%")
                        ->where('issued_by', 'like', "%{$search}%");
                })->orWhere(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('is_deleted', 0)
                        ->where(function ($nestedQuery) use ($search) {
                            $nestedQuery
                                ->where('ticket_code', 'like', "%{$search}%")
                                ->orWhere('violation_type', 'like', "%{$search}%")
                                ->orWhere('issued_by', 'like', "%{$search}%")
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
                });
            }

            $items = $query
                ->orderByDesc('issue_date')
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $items->getCollection()->transform(
                fn (ViolationTicket $item) => $this->transformTicket($item)
            );

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Violation tickets retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve violation tickets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = ViolationTicket::query()
                ->with(['vehicle', 'driver'])
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation ticket not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformTicket($item),
                'message' => 'Violation ticket retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve violation ticket',
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
            $validated = $this->validateTicket($request);

            $item = ViolationTicket::create([
                'project_id' => 1,
                'ticket_code' => $this->generateTicketCode(),
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'violation_type' => $validated['violation_type'],
                'issued_by' => $validated['issued_by'] ?? null,
                'fine_amount' => $validated['fine_amount'] ?? null,
                'points' => $validated['points'] ?? 0,
                'ticket_scope' => $validated['ticket_scope'] ?? 'Business',
                'status' => $validated['status'] ?? 1,
                'is_paid' => $validated['is_paid'] ?? false,
                'payment_method' => $validated['payment_method'] ?? null,
                'paid_date' => $validated['paid_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformTicket($item),
                'message' => 'Violation ticket created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create violation ticket',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = ViolationTicket::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation ticket not found',
                ], 404);
            }

            $validated = $this->validateTicket($request, $item->id);

            $item->update([
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'violation_type' => $validated['violation_type'],
                'issued_by' => $validated['issued_by'] ?? null,
                'fine_amount' => $validated['fine_amount'] ?? null,
                'points' => $validated['points'] ?? 0,
                'ticket_scope' => $validated['ticket_scope'] ?? 'Business',
                'status' => $validated['status'] ?? 1,
                'is_paid' => $validated['is_paid'] ?? false,
                'payment_method' => $validated['payment_method'] ?? null,
                'paid_date' => $validated['paid_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformTicket($item->fresh(['vehicle', 'driver'])),
                'message' => 'Violation ticket updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update violation ticket',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = ViolationTicket::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation ticket not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Violation ticket deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete violation ticket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateTicket(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'driver_id' => 'required|integer|exists:drivers,id',
            'violation_type' => ['required', 'string', Rule::in(ViolationTicket::VIOLATION_TYPES)],
            'issued_by' => 'nullable|string|max:150',
            'fine_amount' => 'nullable|numeric|min:0',
            'points' => 'nullable|integer|min:0|max:20',
            'ticket_scope' => ['nullable', 'string', Rule::in(ViolationTicket::TICKET_SCOPE)],
            'status' => ['nullable', 'integer', Rule::in(array_keys(ViolationTicket::STATUS))],
            'is_paid' => 'nullable|boolean',
            'payment_method' => ['nullable', 'string', Rule::in(ViolationTicket::PAYMENT_METHODS)],
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }

    protected function generateTicketCode(): string
    {
        $count = ViolationTicket::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('TX%06d', 900100 + $count);
    }

    protected function transformTicket(ViolationTicket $item): array
    {
        $vehicle = $item->vehicle;
        $driver = $item->driver;

        return [
            'id' => $item->id,
            'ticket_code' => $item->ticket_code,
            'issue_date' => optional($item->issue_date)->format('Y-m-d'),
            'due_date' => optional($item->due_date)->format('Y-m-d'),
            'vehicle_id' => $item->vehicle_id,
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'make_brand' => $vehicle->make_brand,
                'model' => $vehicle->model,
            ] : null,
            'vehicle_number' => $vehicle?->vehicle_number,
            'vehicle_make_model' => $vehicle ? trim(sprintf('%s %s', $vehicle->make_brand, $vehicle->model)) : null,
            'driver_id' => $item->driver_id,
            'driver' => $driver ? [
                'id' => $driver->id,
                'driver_name' => $driver->driver_name,
                'contact_number' => $driver->contact_number,
            ] : null,
            'driver_name' => $driver?->driver_name,
            'driver_phone' => $driver?->contact_number,
            'violation_type' => $item->violation_type,
            'issued_by' => $item->issued_by,
            'fine_amount' => $item->fine_amount !== null ? (float) $item->fine_amount : null,
            'points' => $item->points,
            'ticket_scope' => $item->ticket_scope,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'is_paid' => (bool) $item->is_paid,
            'payment_method' => $item->payment_method,
            'paid_date' => optional($item->paid_date)->format('Y-m-d'),
            'notes' => $item->notes,
            'created_at' => optional($item->created_at)->toIso8601String(),
            'updated_at' => optional($item->updated_at)->toIso8601String(),
        ];
    }
}
