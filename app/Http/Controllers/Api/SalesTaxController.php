<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesTax;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesTaxController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $items = SalesTax::where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Sales taxes retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales taxes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = SalesTax::where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales tax not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Sales tax retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales tax',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $item = SalesTax::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales tax not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Sales tax retrieved for editing',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales tax',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tax_name' => 'required|string|max:100',
                'tax_type' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5]),
                ],
                'tax_rate' => 'required|numeric|min:0|max:999999.9999',
                'application_reason' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
                ],
                'status_active' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);

            $item = SalesTax::create([
                'project_id' => 1,
                'system_prefix' => 'ST',
                'system_no' => $this->generateSystemNo(),
                'tax_name' => $validated['tax_name'],
                'tax_type' => $validated['tax_type'],
                'tax_rate' => $validated['tax_rate'],
                'application_reason' => $validated['application_reason'],
                'status_active' => $validated['status_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Sales tax created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales tax',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = SalesTax::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales tax not found',
                ], 404);
            }

            $validated = $request->validate([
                'tax_name' => 'required|string|max:100',
                'tax_type' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5]),
                ],
                'tax_rate' => 'required|numeric|min:0|max:999999.9999',
                'application_reason' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
                ],
                'status_active' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);

            $item->update([
                'tax_name' => $validated['tax_name'],
                'tax_type' => $validated['tax_type'],
                'tax_rate' => $validated['tax_rate'],
                'application_reason' => $validated['application_reason'],
                'status_active' => $validated['status_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Sales tax updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales tax',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = SalesTax::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales tax not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales tax deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sales tax',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateSystemNo(): string
    {
        $year = date('Y');
        $count = SalesTax::where('system_no', 'like', "ST-{$year}-%")->count();

        return "ST-{$year}-".str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
