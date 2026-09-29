<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceTerm;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceTermController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $items = InvoiceTerm::where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Invoice terms retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoice terms',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = InvoiceTerm::where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice term not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Invoice term retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoice term',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $item = InvoiceTerm::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice term not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Invoice term retrieved for editing',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoice term',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'note' => 'nullable|string',
                'status' => [
                    'required',
                    'integer',
                    Rule::in([1, 2]),
                ],
            ]);

            $item = InvoiceTerm::create([
                'project_id' => 1,
                'term_id' => $this->generateTermId(),
                'term_name' => $validated['term_name'],
                'description' => $validated['description'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Invoice term created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice term',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = InvoiceTerm::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice term not found',
                ], 404);
            }

            $validated = $request->validate([
                'term_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'note' => 'nullable|string',
                'status' => [
                    'required',
                    'integer',
                    Rule::in([1, 2]),
                ],
            ]);

            $item->update([
                'term_name' => $validated['term_name'],
                'description' => $validated['description'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Invoice term updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice term',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = InvoiceTerm::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice term not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice term deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice term',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateTermId(): string
    {
        $year = date('Y');
        $count = InvoiceTerm::where('term_id', 'like', "TERM-{$year}-%")->count();

        return "TERM-{$year}-".str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
