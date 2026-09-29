<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEstimationRequest;
use App\Http\Requests\Api\UpdateEstimationRequest;
use App\Models\AccountHolder;
use App\Models\Estimation;
use App\Models\EstimationDetail;
use App\Models\JobSite;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstimationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Estimation::with(['customer', 'jobSite', 'details']);

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('estimation_no', 'like', "%{$search}%")
                        ->orWhere('job_description', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('company_name', 'like', "%{$search}%")
                                ->orWhere('account_name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('job_type') && $request->job_type !== '') {
                $query->where('job_type', $request->job_type);
            }

            if ($request->has('customer_id') && $request->customer_id !== '') {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->has('from_date') && $request->from_date !== '') {
                $query->where('issue_date', '>=', $request->from_date);
            }

            if ($request->has('to_date') && $request->to_date !== '') {
                $query->where('issue_date', '<=', $request->to_date);
            }

            $query->orderBy('created_at', 'desc');

            $perPage = $request->per_page ?? 15;
            $items = $query->paginate($perPage);

            $items->getCollection()->transform(function ($item) {
                return $this->transformEstimation($item);
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Estimations retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve estimations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreEstimationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $estimation = DB::transaction(function () use ($validated) {
                $estimationNo = $this->generateEstimationNo();

                $estimation = Estimation::create([
                    'estimation_no' => $estimationNo,
                    'issue_date' => $validated['issue_date'],
                    'expire_date' => $validated['expire_date'],
                    'currency' => $validated['currency'],
                    'status' => $validated['status'],
                    'job_type' => $validated['job_type'],
                    'job_description' => $validated['job_description'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'job_site_id' => $validated['job_site_id'] ?? null,
                    'schedule_start_date' => $validated['schedule_start_date'],
                    'schedule_end_date' => $validated['schedule_end_date'],
                    'scope_of_work' => $validated['scope_of_work'] ?? null,
                    'sub_total' => $validated['sub_total'] ?? 0,
                    'tax' => $validated['tax'] ?? null,
                    'discount' => $validated['discount'] ?? null,
                    'total' => $validated['total'] ?? 0,
                    'note' => $validated['note'] ?? null,
                    'payment_method' => $validated['payment_method'] ?? null,
                    'deposit_required' => $validated['deposit_required'] ?? false,
                    'payment_term' => $validated['payment_term'] ?? null,
                    'customer_approval_date' => $validated['customer_approval_date'] ?? null,
                    'customer_approve_by' => $validated['customer_approve_by'] ?? null,
                    'approval_method' => $validated['approval_method'] ?? null,
                    'convert_to_job_order' => $validated['convert_to_job_order'] ?? false,
                    'customer_type' => $validated['customer_type'] ?? null, 'site_floor_level' => $validated['site_floor_level'] ?? null, 'site_suite_unit' => $validated['site_suite_unit'] ?? null,
                    'site_city' => $validated['site_city'] ?? null, 'site_province' => $validated['site_province'] ?? null, 'site_postal_code' => $validated['site_postal_code'] ?? null, 'site_access_details' => $validated['site_access_details'] ?? null,
                    'tax_rate' => $validated['tax_rate'] ?? null, 'tax_registration_no' => $validated['tax_registration_no'] ?? null, 'payment_methods' => $validated['payment_methods'] ?? null,
                    'deposit_percentage' => $validated['deposit_percentage'] ?? null, 'deposit_amount' => $validated['deposit_amount'] ?? null, 'notes_to_customer' => $validated['notes_to_customer'] ?? null,
                    'invoice_to' => $validated['invoice_to'] ?? null, 'invoice_title' => $validated['invoice_title'] ?? null, 'invoice_prefix' => $validated['invoice_prefix'] ?? null, 'invoice_next_number' => $validated['invoice_next_number'] ?? null, 'create_invoice_after_approval' => $validated['create_invoice_after_approval'] ?? false,
                ]);

                foreach ($validated['details'] as $detail) {
                    EstimationDetail::create([
                        'estimation_id' => $estimation->id,
                        'item_name' => $detail['item_name'],
                        'item_description' => $detail['item_description'] ?? null,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'sale_tax_percentage' => $detail['sale_tax_percentage'] ?? null,
                    ]);
                }

                $estimation->load(['customer', 'jobSite', 'details']);

                return $estimation;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformEstimation($estimation),
                'message' => 'Estimation created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create estimation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $estimation = Estimation::with(['customer', 'jobSite', 'details'])->find($id);

            if (!$estimation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estimation not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformEstimation($estimation),
                'message' => 'Estimation retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve estimation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateEstimationRequest $request, int $id): JsonResponse
    {
        try {
            $estimation = Estimation::find($id);

            if (!$estimation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estimation not found',
                ], 404);
            }

            $validated = $request->validated();

            $estimation = DB::transaction(function () use ($estimation, $validated) {
                $estimation->update([
                    'issue_date' => $validated['issue_date'],
                    'expire_date' => $validated['expire_date'],
                    'currency' => $validated['currency'],
                    'status' => $validated['status'],
                    'job_type' => $validated['job_type'],
                    'job_description' => $validated['job_description'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'job_site_id' => $validated['job_site_id'] ?? null,
                    'schedule_start_date' => $validated['schedule_start_date'],
                    'schedule_end_date' => $validated['schedule_end_date'],
                    'scope_of_work' => $validated['scope_of_work'] ?? null,
                    'sub_total' => $validated['sub_total'] ?? 0,
                    'tax' => $validated['tax'] ?? null,
                    'discount' => $validated['discount'] ?? null,
                    'total' => $validated['total'] ?? 0,
                    'note' => $validated['note'] ?? null,
                    'payment_method' => $validated['payment_method'] ?? null,
                    'deposit_required' => $validated['deposit_required'] ?? false,
                    'payment_term' => $validated['payment_term'] ?? null,
                    'customer_approval_date' => $validated['customer_approval_date'] ?? null,
                    'customer_approve_by' => $validated['customer_approve_by'] ?? null,
                    'approval_method' => $validated['approval_method'] ?? null,
                    'convert_to_job_order' => $validated['convert_to_job_order'] ?? false,
                    'customer_type' => $validated['customer_type'] ?? null, 'site_floor_level' => $validated['site_floor_level'] ?? null, 'site_suite_unit' => $validated['site_suite_unit'] ?? null,
                    'site_city' => $validated['site_city'] ?? null, 'site_province' => $validated['site_province'] ?? null, 'site_postal_code' => $validated['site_postal_code'] ?? null, 'site_access_details' => $validated['site_access_details'] ?? null,
                    'tax_rate' => $validated['tax_rate'] ?? null, 'tax_registration_no' => $validated['tax_registration_no'] ?? null, 'payment_methods' => $validated['payment_methods'] ?? null,
                    'deposit_percentage' => $validated['deposit_percentage'] ?? null, 'deposit_amount' => $validated['deposit_amount'] ?? null, 'notes_to_customer' => $validated['notes_to_customer'] ?? null,
                    'invoice_to' => $validated['invoice_to'] ?? null, 'invoice_title' => $validated['invoice_title'] ?? null, 'invoice_prefix' => $validated['invoice_prefix'] ?? null, 'invoice_next_number' => $validated['invoice_next_number'] ?? null, 'create_invoice_after_approval' => $validated['create_invoice_after_approval'] ?? false,
                ]);

                $estimation->details()->delete();

                foreach ($validated['details'] as $detail) {
                    EstimationDetail::create([
                        'estimation_id' => $estimation->id,
                        'item_name' => $detail['item_name'],
                        'item_description' => $detail['item_description'] ?? null,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'sale_tax_percentage' => $detail['sale_tax_percentage'] ?? null,
                    ]);
                }

                $estimation->load(['customer', 'jobSite', 'details']);

                return $estimation;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformEstimation($estimation),
                'message' => 'Estimation updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update estimation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $estimation = Estimation::find($id);

            if (!$estimation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estimation not found',
                ], 404);
            }

            $estimation->details()->delete();
            $estimation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estimation deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete estimation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function customers(): JsonResponse
    {
        try {
            $customers = AccountHolder::where('account_type', 1)
                ->where('is_deleted', 0)
                ->where('status_active', 1)
                ->select(
                    'id',
                    'account_name',
                    'company_name',
                    'cell_phone',
                    'email',
                    'house_number',
                    'street_number',
                    'city',
                    'state',
                    'country',
                    'zip_code'
                )
                ->orderBy('company_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'Customers retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function jobSites(): JsonResponse
    {
        try {
            $jobSites = JobSite::where('is_deleted', 0)
                ->where('status', 1)
                ->select('id', 'job_site_no', 'job_site_name', 'address', DB::raw("null as map_link"))
                ->orderBy('job_site_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobSites,
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

    public function getOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'currency' => Estimation::CURRENCY,
                'status' => Estimation::STATUS,
                'job_type' => Estimation::JOB_TYPE,
                'payment_method' => Estimation::PAYMENT_METHOD,
                'approval_method' => Estimation::APPROVAL_METHOD,
            ],
            'message' => 'Options retrieved successfully',
        ], 200);
    }

    private function generateEstimationNo(): string
    {
        $year = date('Y');
        $latest = Estimation::where('estimation_no', 'like', "EST-{$year}-%")
            ->orderBy('estimation_no', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->estimation_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "EST-{$year}-{$newNumber}";
    }

    private function transformEstimation(Estimation $estimation): array
    {
        return [
            'id' => $estimation->id,
            'estimation_no' => $estimation->estimation_no,
            'issue_date' => $estimation->issue_date?->format('Y-m-d'),
            'expire_date' => $estimation->expire_date?->format('Y-m-d H:i:s'),
            'currency' => $estimation->currency,
            'currency_label' => $estimation->currency_label,
            'status' => $estimation->status,
            'status_label' => $estimation->status_label,
            'job_type' => $estimation->job_type,
            'job_type_label' => $estimation->job_type_label,
            'job_description' => $estimation->job_description,
            'customer_id' => $estimation->customer_id,
            'customer' => $estimation->customer ? [
                'id' => $estimation->customer->id,
                'name' => $estimation->customer->account_name,
                'company_name' => $estimation->customer->company_name,
                'address' => $estimation->customer->address,
                'contact' => $estimation->customer->cell_phone ?? $estimation->customer->office_phone,
            ] : null,
            'job_site_id' => $estimation->job_site_id,
            'job_site' => $estimation->jobSite ? [
                'id' => $estimation->jobSite->id,
                'name' => $estimation->jobSite->job_site_name,
                'address' => $estimation->jobSite->address,
                'map_link' => $estimation->jobSite->map_link ?? null,
            ] : null,
            'schedule_start_date' => $estimation->schedule_start_date?->format('Y-m-d H:i:s'),
            'schedule_end_date' => $estimation->schedule_end_date?->format('Y-m-d H:i:s'),
            'scope_of_work' => $estimation->scope_of_work,
            'sub_total' => (float) $estimation->sub_total,
            'tax' => $estimation->tax ? (float) $estimation->tax : null,
            'discount' => $estimation->discount ? (float) $estimation->discount : null,
            'total' => (float) $estimation->total,
            'note' => $estimation->note,
            'payment_method' => $estimation->payment_method,
            'payment_method_label' => $estimation->payment_method_label,
            'deposit_required' => $estimation->deposit_required,
            'payment_term' => $estimation->payment_term,
            'customer_approval_date' => $estimation->customer_approval_date?->format('Y-m-d H:i:s'),
            'customer_approve_by' => $estimation->customer_approve_by,
            'approval_method' => $estimation->approval_method,
            'approval_method_label' => $estimation->approval_method_label,
            'convert_to_job_order' => $estimation->convert_to_job_order,
            'customer_type' => $estimation->customer_type, 'site_floor_level' => $estimation->site_floor_level, 'site_suite_unit' => $estimation->site_suite_unit,
            'site_city' => $estimation->site_city, 'site_province' => $estimation->site_province, 'site_postal_code' => $estimation->site_postal_code, 'site_access_details' => $estimation->site_access_details,
            'tax_rate' => $estimation->tax_rate ? (float) $estimation->tax_rate : null, 'tax_registration_no' => $estimation->tax_registration_no, 'payment_methods' => $estimation->payment_methods,
            'deposit_percentage' => $estimation->deposit_percentage ? (float) $estimation->deposit_percentage : null, 'deposit_amount' => $estimation->deposit_amount ? (float) $estimation->deposit_amount : null, 'notes_to_customer' => $estimation->notes_to_customer,
            'invoice_to' => $estimation->invoice_to, 'invoice_title' => $estimation->invoice_title, 'invoice_prefix' => $estimation->invoice_prefix, 'invoice_next_number' => $estimation->invoice_next_number, 'create_invoice_after_approval' => $estimation->create_invoice_after_approval,
            'details' => $estimation->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'item_name' => $detail->item_name,
                    'item_description' => $detail->item_description,
                    'quantity' => (float) $detail->quantity,
                    'unit_price' => (float) $detail->unit_price,
                    'sale_tax_percentage' => $detail->sale_tax_percentage ? (float) $detail->sale_tax_percentage : null,
                    'sale_tax_amount' => (float) $detail->sale_tax_amount,
                    'total' => (float) $detail->total,
                ];
            }),
            'created_at' => $estimation->created_at?->toIso8601String(),
            'updated_at' => $estimation->updated_at?->toIso8601String(),
        ];
    }
}
