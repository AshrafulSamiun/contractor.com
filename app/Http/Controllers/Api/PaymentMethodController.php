<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Get all payment methods
     * GET /api/payment-methods
     */
    public function index(): JsonResponse
    {
        try {
            $items = PaymentMethod::where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Payment methods retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single payment method
     * GET /api/payment-methods/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $item = PaymentMethod::where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Payment method retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment method for editing
     * GET /api/payment-methods/{id}/edit
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $item = PaymentMethod::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Payment method retrieved for editing',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new payment method
     * POST /api/payment-methods
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'payment_type' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5, 6, 7, 8]),
                ],
                'linked_account' => 'nullable|string|max:255',
                'status_active' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);

            $item = PaymentMethod::create([
                'project_id' => 1,
                'system_prefix' => 'PM',
                'system_no' => $this->generateSystemNo(),
                'name' => $validated['name'],
                'payment_type' => $validated['payment_type'],
                'linked_account' => $validated['linked_account'] ?? null,
                'status_active' => $validated['status_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Payment method created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment method',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update payment method
     * PUT /api/payment-methods/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = PaymentMethod::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'payment_type' => [
                    'required',
                    'integer',
                    Rule::in([1, 2, 3, 4, 5, 6, 7, 8]),
                ],
                'linked_account' => 'nullable|string|max:255',
                'status_active' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);

            $item->update([
                'name' => $validated['name'],
                'payment_type' => $validated['payment_type'],
                'linked_account' => $validated['linked_account'] ?? null,
                'status_active' => $validated['status_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Payment method updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete (soft) payment method
     * DELETE /api/payment-methods/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $item = PaymentMethod::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate unique system number
     */
    private function generateSystemNo(): string
    {
        $year = date('Y');
        $count = PaymentMethod::where('system_no', 'like', "PM-{$year}-%")->count();

        return "PM-{$year}-".str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
