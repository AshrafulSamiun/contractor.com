<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ServiceItem::where('is_deleted', 0);

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_no', 'like', "%{$search}%")
                        ->orWhere('item_name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('created_at', 'desc');

            $perPage = $request->per_page ?? 15;
            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Service items retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = ServiceItem::where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Service item retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $item = ServiceItem::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Service item retrieved for editing',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_name' => 'required|string|max:150',
                'unit_of_measure' => 'nullable|string|max:50',
                'price' => 'nullable|numeric|min:0',
                'sales_tax_applicable' => 'nullable|boolean',
                'status' => [
                    'nullable',
                    'integer',
                    Rule::in([1, 2]),
                ],
                'note' => 'nullable|string',
            ]);

            $item = ServiceItem::create([
                'project_id' => 1,
                'item_no' => $this->generateItemNo(),
                'item_name' => $validated['item_name'],
                'unit_of_measure' => $validated['unit_of_measure'] ?? null,
                'price' => isset($validated['price'])
                    ? number_format((float) $validated['price'], 2, '.', '')
                    : 0.00,
                'sales_tax_applicable' => $validated['sales_tax_applicable'] ?? false,
                'status' => $validated['status'] ?? 1,
                'note' => isset($validated['note'])
                    ? trim($validated['note'])
                    : null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Service item created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service item',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = ServiceItem::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service item not found',
                ], 404);
            }

            $validated = $request->validate([
                'item_name' => 'required|string|max:150',
                'unit_of_measure' => 'nullable|string|max:50',
                'price' => 'nullable|numeric|min:0',
                'sales_tax_applicable' => 'nullable|boolean',
                'status' => [
                    'nullable',
                    'integer',
                    Rule::in([1, 2]),
                ],
                'note' => 'nullable|string',
            ]);

            $item->update([
                'item_name' => $validated['item_name'],
                'unit_of_measure' => $validated['unit_of_measure'] ?? null,
                'price' => isset($validated['price'])
                    ? number_format((float) $validated['price'], 2, '.', '')
                    : 0.00,
                'sales_tax_applicable' => $validated['sales_tax_applicable'] ?? false,
                'status' => $validated['status'] ?? 1,
                'note' => isset($validated['note'])
                    ? trim($validated['note'])
                    : null,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Service item updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service item',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = ServiceItem::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service item not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service item deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $item = ServiceItem::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service item not found',
                ], 404);
            }

            $newStatus = $item->status === 1 ? 2 : 1;
            $item->update([
                'status' => $newStatus,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Status toggled successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateItemNo(): string
    {
        $year = date('Y');
        $count = ServiceItem::where('item_no', 'like', "SER-{$year}-%")->count();

        return 'SER-'.$year.'-'.str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
