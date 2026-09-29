<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEstimationRequest;
use App\Http\Requests\Api\UpdateEstimationRequest;
use App\Models\AccountHolder;
use App\Models\Estimation;
use App\Models\JobSite;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Quotation::with(['customer', 'jobSite', 'details', 'estimation']);

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('quotation_no', 'like', "%{$search}%")
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

            if ($request->has('estimation_id') && $request->estimation_id !== '') {
                $query->where('estimation_id', $request->estimation_id);
            }

            $query->orderBy('created_at', 'desc');

            $perPage = $request->per_page ?? 15;
            $items = $query->paginate($perPage);

            $items->getCollection()->transform(function ($item) {
                return $this->transformQuotation($item);
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Quotations retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quotations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreEstimationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $quotation = DB::transaction(function () use ($validated) {
                $quotationNo = $this->generateQuotationNo();

                $quotation = Quotation::create([
                    'estimation_id' => $validated['estimation_id'] ?? null,
                    'quotation_no' => $quotationNo,
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
                ]);

                foreach ($validated['details'] as $detail) {
                    QuotationDetail::create([
                        'quotation_id' => $quotation->id,
                        'item_name' => $detail['item_name'],
                        'item_description' => $detail['item_description'] ?? null,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'sale_tax_percentage' => $detail['sale_tax_percentage'] ?? null,
                    ]);
                }

                $quotation->load(['customer', 'jobSite', 'details', 'estimation']);

                return $quotation;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformQuotation($quotation),
                'message' => 'Quotation created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $quotation = Quotation::with(['customer', 'jobSite', 'details', 'estimation'])->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformQuotation($quotation),
                'message' => 'Quotation retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quotation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateEstimationRequest $request, int $id): JsonResponse
    {
        try {
            $quotation = Quotation::find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            $validated = $request->validated();

            $quotation = DB::transaction(function () use ($quotation, $validated) {
                $quotation->update([
                    'estimation_id' => $validated['estimation_id'] ?? $quotation->estimation_id,
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
                ]);

                $quotation->details()->delete();

                foreach ($validated['details'] as $detail) {
                    QuotationDetail::create([
                        'quotation_id' => $quotation->id,
                        'item_name' => $detail['item_name'],
                        'item_description' => $detail['item_description'] ?? null,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'sale_tax_percentage' => $detail['sale_tax_percentage'] ?? null,
                    ]);
                }

                $quotation->load(['customer', 'jobSite', 'details', 'estimation']);

                return $quotation;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformQuotation($quotation),
                'message' => 'Quotation updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quotation',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $quotation = Quotation::find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            $quotation->details()->delete();
            $quotation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quotation deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quotation',
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
                ->select('id', 'account_name', 'company_name', 'cell_phone', 'email',
                    DB::raw("CONCAT_WS(', ', house_number, street_number, city, state, country, zip_code) as address"))
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
                ->select('id', 'job_site_no', 'job_site_name', 'address', 'map_link')
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
                'currency' => Quotation::CURRENCY,
                'status' => Quotation::STATUS,
                'job_type' => Quotation::JOB_TYPE,
                'payment_method' => Quotation::PAYMENT_METHOD,
                'approval_method' => Quotation::APPROVAL_METHOD,
            ],
            'message' => 'Options retrieved successfully',
        ], 200);
    }

    public function estimations(): JsonResponse
    {
        try {
            $estimations = Estimation::where('is_deleted', 0)
                ->where('status', 1)
                ->select('id', 'estimation_no', 'job_description', 'customer_id')
                ->orderBy('estimation_no', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $estimations,
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

    private function generateQuotationNo(): string
    {
        $year = date('Y');
        $latest = Quotation::where('quotation_no', 'like', "QUO-{$year}-%")
            ->orderBy('quotation_no', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->quotation_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "QUO-{$year}-{$newNumber}";
    }

    private function transformQuotation(Quotation $quotation): array
    {
        return [
            'id' => $quotation->id,
            'estimation_id' => $quotation->estimation_id,
            'estimation' => $quotation->estimation ? [
                'id' => $quotation->estimation->id,
                'estimation_no' => $quotation->estimation->estimation_no,
            ] : null,
            'quotation_no' => $quotation->quotation_no,
            'issue_date' => $quotation->issue_date?->format('Y-m-d'),
            'expire_date' => $quotation->expire_date?->format('Y-m-d H:i:s'),
            'currency' => $quotation->currency,
            'currency_label' => $quotation->currency_label,
            'status' => $quotation->status,
            'status_label' => $quotation->status_label,
            'job_type' => $quotation->job_type,
            'job_type_label' => $quotation->job_type_label,
            'job_description' => $quotation->job_description,
            'customer_id' => $quotation->customer_id,
            'customer' => $quotation->customer ? [
                'id' => $quotation->customer->id,
                'name' => $quotation->customer->account_name,
                'company_name' => $quotation->customer->company_name,
                'address' => $quotation->customer->address,
                'contact' => $quotation->customer->cell_phone ?? $quotation->customer->office_phone,
            ] : null,
            'job_site_id' => $quotation->job_site_id,
            'job_site' => $quotation->jobSite ? [
                'id' => $quotation->jobSite->id,
                'name' => $quotation->jobSite->job_site_name,
                'address' => $quotation->jobSite->address,
                'map_link' => $quotation->jobSite->map_link ?? null,
            ] : null,
            'schedule_start_date' => $quotation->schedule_start_date?->format('Y-m-d H:i:s'),
            'schedule_end_date' => $quotation->schedule_end_date?->format('Y-m-d H:i:s'),
            'scope_of_work' => $quotation->scope_of_work,
            'sub_total' => (float) $quotation->sub_total,
            'tax' => $quotation->tax ? (float) $quotation->tax : null,
            'discount' => $quotation->discount ? (float) $quotation->discount : null,
            'total' => (float) $quotation->total,
            'note' => $quotation->note,
            'payment_method' => $quotation->payment_method,
            'payment_method_label' => $quotation->payment_method_label,
            'deposit_required' => $quotation->deposit_required,
            'payment_term' => $quotation->payment_term,
            'customer_approval_date' => $quotation->customer_approval_date?->format('Y-m-d H:i:s'),
            'customer_approve_by' => $quotation->customer_approve_by,
            'approval_method' => $quotation->approval_method,
            'approval_method_label' => $quotation->approval_method_label,
            'convert_to_job_order' => $quotation->convert_to_job_order,
            'details' => $quotation->details->map(function ($detail) {
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
            'created_at' => $quotation->created_at?->toIso8601String(),
            'updated_at' => $quotation->updated_at?->toIso8601String(),
        ];
    }
}
