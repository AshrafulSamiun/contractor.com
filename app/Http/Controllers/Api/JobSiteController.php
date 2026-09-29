<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSite;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobSiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = JobSite::where('is_deleted', 0);

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('job_site_no', 'like', "%{$search}%")
                        ->orWhere('job_site_name', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_no', 'like', "%{$search}%");
                });
            }

            $query->orderBy('created_at', 'desc');

            $perPage = $request->per_page ?? 15;
            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Job sites retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job sites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = JobSite::where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job site not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Job site retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job site',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $item = JobSite::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job site not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Job site retrieved for editing',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job site',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'job_site_name' => 'required|string|max:150',
                'contact_no' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'description' => 'nullable|string',
                'customer_no' => 'required|string|max:50',
                'customer_name' => 'required|string|max:150',
                'address' => 'nullable|string',
                'contact_person' => 'required|string|max:150',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:100',
                'status' => [
                    'nullable',
                    'integer',
                    Rule::in([1, 2]),
                ],
                'note' => 'nullable|string',
            ]);

            $item = JobSite::create([
                'project_id' => 1,
                'job_site_no' => $this->generateJobSiteNo(),
                'job_site_name' => $validated['job_site_name'],
                'contact_no' => $validated['contact_no'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'customer_no' => $validated['customer_no'],
                'customer_name' => $validated['customer_name'],
                'address' => $validated['address'] ?? null,
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'] ?? 1,
                'note' => isset($validated['note']) ? trim($validated['note']) : null,
                'inserted_by' => 0,
                'updated_by' => 0,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Job site created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create job site',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = JobSite::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job site not found',
                ], 404);
            }

            $validated = $request->validate([
                'job_site_name' => 'required|string|max:150',
                'contact_no' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'description' => 'nullable|string',
                'customer_no' => 'required|string|max:50',
                'customer_name' => 'required|string|max:150',
                'address' => 'nullable|string',
                'contact_person' => 'required|string|max:150',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:100',
                'status' => [
                    'nullable',
                    'integer',
                    Rule::in([1, 2]),
                ],
                'note' => 'nullable|string',
            ]);

            $item->update([
                'job_site_name' => $validated['job_site_name'],
                'contact_no' => $validated['contact_no'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'customer_no' => $validated['customer_no'],
                'customer_name' => $validated['customer_name'],
                'address' => $validated['address'] ?? null,
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'] ?? 1,
                'note' => isset($validated['note']) ? trim($validated['note']) : null,
                'updated_by' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Job site updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job site',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = JobSite::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job site not found',
                ], 404);
            }

            $item->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job site deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job site',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $item = JobSite::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job site not found',
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

    private function generateJobSiteNo(): string
    {
        $year = date('Y');
        $count = JobSite::where('job_site_no', 'like', "JS-{$year}-%")->count();

        return 'JS-'.$year.'-'.str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
