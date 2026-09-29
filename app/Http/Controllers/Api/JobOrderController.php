<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJobOrderRequest;
use App\Http\Requests\Api\UpdateJobOrderRequest;
use App\Models\AccountHolder;
use App\Models\Estimation;
use App\Models\JobOrder;
use App\Models\JobOrderDetail;
use App\Models\JobSite;
use App\Models\Quotation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = JobOrder::with(['customer', 'jobSite', 'details', 'quotation', 'estimation']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('job_order_no', 'like', "%{$search}%")
                        ->orWhere('job_description', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('company_name', 'like', "%{$search}%")
                                ->orWhere('account_name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->integer('status'));
            }

            $items = $query->latest()->paginate($request->integer('per_page', 15));
            $items->getCollection()->transform(fn (JobOrder $item) => $this->transformJobOrder($item));

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Job orders retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreJobOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $jobOrder = DB::transaction(function () use ($validated) {
                $payload = $this->preparePayload($validated);
                $jobOrder = JobOrder::create([
                    ...$payload,
                    'job_order_no' => $this->generateJobOrderNo(),
                ]);

                $this->syncDetails($jobOrder, $validated['details']);
                $this->syncSourceFlags($jobOrder);
                $jobOrder->load(['customer', 'jobSite', 'details', 'quotation', 'estimation']);

                return $jobOrder;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformJobOrder($jobOrder),
                'message' => 'Job order created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create job order',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $jobOrder = JobOrder::with(['customer', 'jobSite', 'details', 'quotation', 'estimation'])->find($id);

            if (!$jobOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job order not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformJobOrder($jobOrder),
                'message' => 'Job order retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateJobOrderRequest $request, int $id): JsonResponse
    {
        try {
            $jobOrder = JobOrder::find($id);

            if (!$jobOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job order not found',
                ], 404);
            }

            $validated = $request->validated();

            $jobOrder = DB::transaction(function () use ($jobOrder, $validated) {
                $payload = $this->preparePayload($validated);
                $jobOrder->update($payload);
                $jobOrder->details()->delete();
                $this->syncDetails($jobOrder, $validated['details']);
                $this->syncSourceFlags($jobOrder);
                $jobOrder->load(['customer', 'jobSite', 'details', 'quotation', 'estimation']);

                return $jobOrder;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformJobOrder($jobOrder),
                'message' => 'Job order updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job order',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $jobOrder = JobOrder::find($id);

            if (!$jobOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job order not found',
                ], 404);
            }

            $jobOrder->details()->delete();
            $jobOrder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job order deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function customers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AccountHolder::where('account_type', 1)
                ->where('is_deleted', 0)
                ->where('status_active', 1)
                ->select('id', 'account_name', 'company_name', 'cell_phone', 'email', 'house_number', 'street_number', 'city', 'state')
                ->orderBy('company_name')
                ->get(),
        ]);
    }

    public function jobSites(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => JobSite::where('is_deleted', 0)
                ->where('status', 1)
                ->select('id', 'job_site_no', 'job_site_name', 'address', 'map_link')
                ->orderBy('job_site_name')
                ->get(),
        ]);
    }

    public function quotations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Quotation::select('id', 'quotation_no', 'job_description', 'customer_id', 'job_site_id', 'status')
                ->latest()
                ->get(),
        ]);
    }

    public function estimations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Estimation::select('id', 'estimation_no', 'job_description', 'customer_id', 'job_site_id', 'status')
                ->latest()
                ->get(),
        ]);
    }

    public function getOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status' => JobOrder::STATUS,
                'job_type' => JobOrder::JOB_TYPE,
                'payment_method' => JobOrder::PAYMENT_METHOD,
                'payment_status' => JobOrder::PAYMENT_STATUS,
            ],
        ]);
    }

    private function preparePayload(array $validated): array
    {
        $source = $this->resolveSource($validated);
        $details = collect($validated['details']);
        $subTotal = $details->sum(function (array $detail) {
            $subtotal = (float) $detail['quantity'] * (float) $detail['unit_price'];
            $taxAmount = $subtotal * (((float) ($detail['sale_tax_percentage'] ?? 0)) / 100);
            return $subtotal + $taxAmount;
        });
        $tax = (float) ($validated['tax'] ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);
        $total = max($subTotal + $tax - $discount, 0);
        $amountPaid = (float) ($validated['amount_paid'] ?? 0);

        $workflowFields = collect($validated)->only([
            'job_order_time', 'priority', 'customer_approved', 'customer_type', 'site_floor_level',
            'site_suite_unit', 'site_city', 'site_province', 'site_postal_code', 'site_access_details',
            'request_date', 'request_time', 'requested_by', 'request_method', 'reference_no', 'account_no',
            'alternate_date', 'alternate_start_time', 'alternate_end_time', 'alternate_duration', 'service_notes',
            'access_requirements', 'key_fob_required', 'key_provided_by', 'equipment_required', 'permits_required',
            'permit_details', 'safety_requirements', 'insurance_provided', 'insurance_expiry_date', 'wcb_no', 'customer_notes',
        ])->toArray();

        return [
            'quotation_id' => $validated['quotation_id'] ?? null,
            'estimation_id' => $validated['estimation_id'] ?? null,
            'issue_date' => $validated['issue_date'],
            'status' => $validated['status'],
            'job_type' => $validated['job_type'] ?? $source['job_type'],
            'job_description' => $validated['job_description'] ?? $source['job_description'],
            'customer_id' => $validated['customer_id'] ?? $source['customer_id'],
            'job_site_id' => $validated['job_site_id'] ?? $source['job_site_id'],
            'site_contact_person' => $validated['site_contact_person'] ?? null,
            'site_contact_number' => $validated['site_contact_number'] ?? null,
            'map_link' => $validated['map_link'] ?? $source['map_link'],
            'schedule_start_date' => $validated['schedule_start_date'] ?? $source['schedule_start_date'],
            'schedule_end_date' => $validated['schedule_end_date'] ?? $source['schedule_end_date'],
            'scope_of_work' => $validated['scope_of_work'] ?? $source['scope_of_work'],
            'sub_total' => $subTotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'note' => $validated['note'] ?? null,
            'payment_method' => $validated['payment_method'] ?? $source['payment_method'],
            'deposit_received' => (float) ($validated['deposit_received'] ?? 0),
            'amount_paid' => $amountPaid,
            'outstanding_balance' => max($total - $amountPaid, 0),
            'payment_status' => $validated['payment_status'],
            'progress' => (int) ($validated['progress'] ?? 0),
            'converted_to_invoice' => (bool) ($validated['converted_to_invoice'] ?? false),
            'invoice_reference' => $validated['invoice_reference'] ?? null,
            ...$workflowFields,
        ];
    }

    private function resolveSource(array $validated): array
    {
        $defaults = [
            'job_type' => null,
            'job_description' => null,
            'customer_id' => null,
            'job_site_id' => null,
            'map_link' => null,
            'schedule_start_date' => null,
            'schedule_end_date' => null,
            'scope_of_work' => null,
            'payment_method' => null,
        ];

        if (!empty($validated['quotation_id'])) {
            $quotation = Quotation::with('jobSite')->find($validated['quotation_id']);
            if ($quotation) {
                return [
                    'job_type' => $quotation->job_type,
                    'job_description' => $quotation->job_description,
                    'customer_id' => $quotation->customer_id,
                    'job_site_id' => $quotation->job_site_id,
                    'map_link' => $quotation->jobSite?->map_link,
                    'schedule_start_date' => $quotation->schedule_start_date,
                    'schedule_end_date' => $quotation->schedule_end_date,
                    'scope_of_work' => $quotation->scope_of_work,
                    'payment_method' => $quotation->payment_method,
                ];
            }
        }

        if (!empty($validated['estimation_id'])) {
            $estimation = Estimation::with('jobSite')->find($validated['estimation_id']);
            if ($estimation) {
                return [
                    'job_type' => $estimation->job_type,
                    'job_description' => $estimation->job_description,
                    'customer_id' => $estimation->customer_id,
                    'job_site_id' => $estimation->job_site_id,
                    'map_link' => $estimation->jobSite?->map_link,
                    'schedule_start_date' => $estimation->schedule_start_date,
                    'schedule_end_date' => $estimation->schedule_end_date,
                    'scope_of_work' => $estimation->scope_of_work,
                    'payment_method' => $estimation->payment_method,
                ];
            }
        }

        return $defaults;
    }

    private function syncDetails(JobOrder $jobOrder, array $details): void
    {
        foreach ($details as $detail) {
            JobOrderDetail::create([
                'job_order_id' => $jobOrder->id,
                'item_name' => $detail['item_name'],
                'item_description' => $detail['item_description'] ?? null,
                'quantity' => $detail['quantity'],
                'unit_price' => $detail['unit_price'],
                'sale_tax_percentage' => $detail['sale_tax_percentage'] ?? 0,
            ]);
        }
    }

    private function syncSourceFlags(JobOrder $jobOrder): void
    {
        if ($jobOrder->quotation_id) {
            Quotation::whereKey($jobOrder->quotation_id)->update(['convert_to_job_order' => true]);
        }

        if ($jobOrder->estimation_id) {
            Estimation::whereKey($jobOrder->estimation_id)->update(['convert_to_job_order' => true]);
        }
    }

    private function generateJobOrderNo(): string
    {
        $year = date('Y');
        $latest = JobOrder::where('job_order_no', 'like', "JOB-{$year}-%")
            ->orderBy('job_order_no', 'desc')
            ->first();

        $next = $latest ? ((int) substr($latest->job_order_no, -4)) + 1 : 1;

        return sprintf('JOB-%s-%04d', $year, $next);
    }

    private function transformJobOrder(JobOrder $jobOrder): array
    {
        return [
            'id' => $jobOrder->id,
            'job_order_no' => $jobOrder->job_order_no,
            'quotation_id' => $jobOrder->quotation_id,
            'estimation_id' => $jobOrder->estimation_id,
            'quotation' => $jobOrder->quotation ? [
                'id' => $jobOrder->quotation->id,
                'quotation_no' => $jobOrder->quotation->quotation_no,
            ] : null,
            'estimation' => $jobOrder->estimation ? [
                'id' => $jobOrder->estimation->id,
                'estimation_no' => $jobOrder->estimation->estimation_no,
            ] : null,
            'issue_date' => $jobOrder->issue_date?->format('Y-m-d'),
            'status' => $jobOrder->status,
            'status_label' => $jobOrder->status_label,
            'job_type' => $jobOrder->job_type,
            'job_type_label' => $jobOrder->job_type_label,
            'job_description' => $jobOrder->job_description,
            'customer_id' => $jobOrder->customer_id,
            'customer' => $jobOrder->customer ? [
                'id' => $jobOrder->customer->id,
                'name' => $jobOrder->customer->account_name,
                'company_name' => $jobOrder->customer->company_name,
                'contact' => $jobOrder->customer->cell_phone,
                'email' => $jobOrder->customer->email,
                'address' => trim(implode(', ', array_filter([
                    $jobOrder->customer->house_number,
                    $jobOrder->customer->street_number,
                    $jobOrder->customer->city,
                    $jobOrder->customer->state,
                ]))),
            ] : null,
            'job_site_id' => $jobOrder->job_site_id,
            'job_site' => $jobOrder->jobSite ? [
                'id' => $jobOrder->jobSite->id,
                'name' => $jobOrder->jobSite->job_site_name,
                'address' => $jobOrder->jobSite->address,
                'map_link' => $jobOrder->jobSite->map_link,
            ] : null,
            'site_contact_person' => $jobOrder->site_contact_person,
            'site_contact_number' => $jobOrder->site_contact_number,
            'map_link' => $jobOrder->map_link,
            'schedule_start_date' => $jobOrder->schedule_start_date?->format('Y-m-d H:i:s'),
            'schedule_end_date' => $jobOrder->schedule_end_date?->format('Y-m-d H:i:s'),
            'scope_of_work' => $jobOrder->scope_of_work,
            'sub_total' => (float) $jobOrder->sub_total,
            'tax' => (float) $jobOrder->tax,
            'discount' => (float) $jobOrder->discount,
            'total' => (float) $jobOrder->total,
            'note' => $jobOrder->note,
            'payment_method' => $jobOrder->payment_method,
            'payment_method_label' => $jobOrder->payment_method_label,
            'deposit_received' => (float) $jobOrder->deposit_received,
            'amount_paid' => (float) $jobOrder->amount_paid,
            'outstanding_balance' => (float) $jobOrder->outstanding_balance,
            'payment_status' => $jobOrder->payment_status,
            'payment_status_label' => $jobOrder->payment_status_label,
            'progress' => (int) $jobOrder->progress,
            'converted_to_invoice' => $jobOrder->converted_to_invoice,
            'invoice_reference' => $jobOrder->invoice_reference,
            ...$jobOrder->only([
                'job_order_time', 'priority', 'customer_approved', 'customer_type', 'site_floor_level', 'site_suite_unit',
                'site_city', 'site_province', 'site_postal_code', 'site_access_details', 'request_date', 'request_time',
                'requested_by', 'request_method', 'reference_no', 'account_no', 'alternate_date', 'alternate_start_time',
                'alternate_end_time', 'alternate_duration', 'service_notes', 'access_requirements', 'key_fob_required',
                'key_provided_by', 'equipment_required', 'permits_required', 'permit_details', 'safety_requirements',
                'insurance_provided', 'insurance_expiry_date', 'wcb_no', 'customer_notes',
            ]),
            'details' => $jobOrder->details->map(fn (JobOrderDetail $detail) => [
                'id' => $detail->id,
                'item_name' => $detail->item_name,
                'item_description' => $detail->item_description,
                'quantity' => (float) $detail->quantity,
                'unit_price' => (float) $detail->unit_price,
                'sale_tax_percentage' => (float) $detail->sale_tax_percentage,
                'sale_tax_amount' => (float) $detail->sale_tax_amount,
                'total' => (float) $detail->total,
            ]),
            'created_at' => $jobOrder->created_at?->toIso8601String(),
            'updated_at' => $jobOrder->updated_at?->toIso8601String(),
        ];
    }
}
