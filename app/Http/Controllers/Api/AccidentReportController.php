<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccidentReport;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccidentReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AccidentReport::query()
                ->with(['vehicle', 'driver'])
                ->where('is_deleted', 0);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($request->filled('accident_type')) {
                $query->where('accident_type', $request->accident_type);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('report_code', 'like', "%{$search}%")
                        ->orWhere('incident_report_no', 'like', "%{$search}%")
                        ->orWhere('incident_report_name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('accident_type', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                            $vehicleQuery
                                ->where('vehicle_number', 'like', "%{$search}%")
                                ->orWhere('make_brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        })
                        ->orWhereHas('driver', function ($driverQuery) use ($search) {
                            $driverQuery
                                ->where('driver_name', 'like', "%{$search}%")
                                ->orWhere('contact_number', 'like', "%{$search}%");
                        });
                });
            }

            $items = $query
                ->orderByDesc('report_date')
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $items->getCollection()->transform(
                fn (AccidentReport $item) => $this->transformAccident($item)
            );

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Accident reports retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve accident reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = AccidentReport::query()
                ->with(['vehicle', 'driver'])
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accident report not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformAccident($item),
                'message' => 'Accident report retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve accident report',
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
            $validated = $this->validateAccident($request);
            $totals = $this->calculateTotals($validated);

            $item = AccidentReport::create([
                'project_id' => 1,
                'report_code' => $this->generateReportCode(),
                'report_date' => $validated['report_date'] ?? now()->toDateString(),
                'created_by_name' => $validated['created_by_name'] ?? null,
                'incident_report_no' => $validated['incident_report_no'] ?? null,
                'incident_report_name' => $validated['incident_report_name'] ?? null,
                'accident_reference' => $validated['accident_reference'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'location' => $validated['location'] ?? null,
                'accident_type' => $validated['accident_type'],
                'description' => $validated['description'] ?? null,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'] ?? null,
                'at_fault_name' => $validated['at_fault_name'] ?? null,
                'at_fault_position' => $validated['at_fault_position'] ?? null,
                'repair_shop' => $validated['repair_shop'] ?? null,
                'invoice_no' => $validated['invoice_no'] ?? null,
                'invoice_date' => $validated['invoice_date'] ?? null,
                'subtotal_repair_cost' => $totals['subtotal_repair_cost'],
                'sales_tax_rate' => $totals['sales_tax_rate'],
                'sales_tax_amount' => $totals['sales_tax_amount'],
                'total_cost' => $totals['total_cost'],
                'payment_method' => $validated['payment_method'] ?? null,
                'is_paid' => $validated['is_paid'] ?? false,
                'paid_by' => $validated['paid_by'] ?? null,
                'amount_paid_by_company' => $validated['amount_paid_by_company'] ?? null,
                'amount_paid_by_insurance' => $validated['amount_paid_by_insurance'] ?? null,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformAccident($item),
                'message' => 'Accident report created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create accident report',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = AccidentReport::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accident report not found',
                ], 404);
            }

            $validated = $this->validateAccident($request, $item->id);
            $totals = $this->calculateTotals($validated);

            $item->update([
                'report_date' => $validated['report_date'] ?? $item->report_date,
                'created_by_name' => $validated['created_by_name'] ?? null,
                'incident_report_no' => $validated['incident_report_no'] ?? null,
                'incident_report_name' => $validated['incident_report_name'] ?? null,
                'accident_reference' => $validated['accident_reference'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'location' => $validated['location'] ?? null,
                'accident_type' => $validated['accident_type'],
                'description' => $validated['description'] ?? null,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'] ?? null,
                'at_fault_name' => $validated['at_fault_name'] ?? null,
                'at_fault_position' => $validated['at_fault_position'] ?? null,
                'repair_shop' => $validated['repair_shop'] ?? null,
                'invoice_no' => $validated['invoice_no'] ?? null,
                'invoice_date' => $validated['invoice_date'] ?? null,
                'subtotal_repair_cost' => $totals['subtotal_repair_cost'],
                'sales_tax_rate' => $totals['sales_tax_rate'],
                'sales_tax_amount' => $totals['sales_tax_amount'],
                'total_cost' => $totals['total_cost'],
                'payment_method' => $validated['payment_method'] ?? null,
                'is_paid' => $validated['is_paid'] ?? false,
                'paid_by' => $validated['paid_by'] ?? null,
                'amount_paid_by_company' => $validated['amount_paid_by_company'] ?? null,
                'amount_paid_by_insurance' => $validated['amount_paid_by_insurance'] ?? null,
                'status' => $validated['status'] ?? 1,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            $item->load(['vehicle', 'driver']);

            return response()->json([
                'success' => true,
                'data' => $this->transformAccident($item->fresh(['vehicle', 'driver'])),
                'message' => 'Accident report updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update accident report',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = AccidentReport::find($id);

            if (! $item || $item->is_deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accident report not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accident report deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete accident report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateAccident(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'report_date' => 'nullable|date',
            'created_by_name' => 'nullable|string|max:150',
            'incident_report_no' => ['nullable', 'string', 'max:100'],
            'incident_report_name' => 'nullable|string|max:255',
            'accident_reference' => 'nullable|string|max:100',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'accident_type' => ['required', 'string', Rule::in(AccidentReport::ACCIDENT_TYPES)],
            'description' => 'nullable|string',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'driver_id' => 'nullable|integer|exists:drivers,id',
            'at_fault_name' => 'nullable|string|max:150',
            'at_fault_position' => ['nullable', 'string', Rule::in(AccidentReport::FAULT_POSITIONS)],
            'repair_shop' => 'nullable|string|max:150',
            'invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'subtotal_repair_cost' => 'nullable|numeric|min:0',
            'sales_tax_rate' => 'nullable|numeric|min:0|max:100',
            'payment_method' => ['nullable', 'string', Rule::in(AccidentReport::PAYMENT_METHODS)],
            'is_paid' => 'nullable|boolean',
            'paid_by' => 'nullable|string|max:150',
            'amount_paid_by_company' => 'nullable|numeric|min:0',
            'amount_paid_by_insurance' => 'nullable|numeric|min:0',
            'status' => ['nullable', 'integer', Rule::in(array_keys(AccidentReport::STATUS))],
            'notes' => 'nullable|string',
        ]);
    }

    protected function calculateTotals(array $validated): array
    {
        $subtotal = isset($validated['subtotal_repair_cost'])
            ? (float) $validated['subtotal_repair_cost']
            : 0.0;
        $taxRate = isset($validated['sales_tax_rate'])
            ? (float) $validated['sales_tax_rate']
            : 13.0;
        $taxAmount = round(($subtotal * $taxRate) / 100, 2);

        return [
            'subtotal_repair_cost' => $subtotal ?: null,
            'sales_tax_rate' => $taxRate,
            'sales_tax_amount' => $taxAmount,
            'total_cost' => round($subtotal + $taxAmount, 2),
        ];
    }

    protected function generateReportCode(): string
    {
        $year = now()->format('Y');
        $count = AccidentReport::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('ACC-%s-%03d', $year, $count);
    }

    protected function transformAccident(AccidentReport $item): array
    {
        $vehicle = $item->vehicle;
        $driver = $item->driver;

        return [
            'id' => $item->id,
            'report_code' => $item->report_code,
            'report_date' => optional($item->report_date)->format('Y-m-d'),
            'created_by_name' => $item->created_by_name,
            'incident_report_no' => $item->incident_report_no,
            'incident_report_name' => $item->incident_report_name,
            'accident_reference' => $item->accident_reference,
            'incident_date' => optional($item->incident_date)->format('Y-m-d'),
            'incident_time' => $item->incident_time,
            'location' => $item->location,
            'accident_type' => $item->accident_type,
            'description' => $item->description,
            'vehicle_id' => $item->vehicle_id,
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'make_brand' => $vehicle->make_brand,
                'model' => $vehicle->model,
                'plate_number' => $vehicle->plate_number,
            ] : null,
            'vehicle_number' => $vehicle?->vehicle_number,
            'vehicle_make_model' => $vehicle ? trim(sprintf('%s %s', $vehicle->make_brand, $vehicle->model)) : null,
            'plate_number' => $vehicle?->plate_number,
            'driver_id' => $item->driver_id,
            'driver' => $driver ? [
                'id' => $driver->id,
                'driver_name' => $driver->driver_name,
                'contact_number' => $driver->contact_number,
            ] : null,
            'driver_name' => $driver?->driver_name,
            'driver_phone' => $driver?->contact_number,
            'at_fault_name' => $item->at_fault_name,
            'at_fault_position' => $item->at_fault_position,
            'repair_shop' => $item->repair_shop,
            'invoice_no' => $item->invoice_no,
            'invoice_date' => optional($item->invoice_date)->format('Y-m-d'),
            'subtotal_repair_cost' => $item->subtotal_repair_cost !== null ? (float) $item->subtotal_repair_cost : null,
            'sales_tax_rate' => $item->sales_tax_rate !== null ? (float) $item->sales_tax_rate : null,
            'sales_tax_amount' => $item->sales_tax_amount !== null ? (float) $item->sales_tax_amount : null,
            'total_cost' => $item->total_cost !== null ? (float) $item->total_cost : null,
            'payment_method' => $item->payment_method,
            'is_paid' => (bool) $item->is_paid,
            'paid_by' => $item->paid_by,
            'amount_paid_by_company' => $item->amount_paid_by_company !== null ? (float) $item->amount_paid_by_company : null,
            'amount_paid_by_insurance' => $item->amount_paid_by_insurance !== null ? (float) $item->amount_paid_by_insurance : null,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'notes' => $item->notes,
            'created_at' => optional($item->created_at)->toIso8601String(),
            'updated_at' => optional($item->updated_at)->toIso8601String(),
        ];
    }
}
