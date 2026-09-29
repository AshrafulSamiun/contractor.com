<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJobOrderDailyWorkLogRequest;
use App\Http\Requests\Api\UpdateJobOrderDailyWorkLogRequest;
use App\Models\JobOrder;
use App\Models\JobOrderDailyWorkLog;
use App\Models\JobOrderDailyWorkLogEntry;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobOrderDailyWorkLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = JobOrderDailyWorkLog::with(['jobOrder.customer', 'jobOrder.jobSite', 'entries']);

            if ($request->filled('job_order_id')) {
                $query->where('job_order_id', $request->integer('job_order_id'));
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('log_no', 'like', "%{$search}%")
                        ->orWhereHas('jobOrder', function ($jobOrderQuery) use ($search) {
                            $jobOrderQuery->where('job_order_no', 'like', "%{$search}%")
                                ->orWhere('job_description', 'like', "%{$search}%");
                        });
                });
            }

            $logs = $query->latest()->paginate($request->integer('per_page', 30));
            $logs->getCollection()->transform(fn (JobOrderDailyWorkLog $log) => $this->transformLog($log));

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Daily work logs retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily work logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreJobOrderDailyWorkLogRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $log = DB::transaction(function () use ($validated) {
                $totals = $this->calculateTotals($validated['entries']);
                $log = JobOrderDailyWorkLog::create([
                    'job_order_id' => $validated['job_order_id'],
                    'log_no' => $this->generateLogNo(),
                    'period_start' => $validated['period_start'],
                    'period_end' => $validated['period_end'],
                    'project_manager' => $validated['project_manager'] ?? null,
                    'total_wages' => $totals['wages'],
                    'total_materials' => $totals['materials'],
                    'total_overhead' => $totals['overhead'],
                    'total_cost' => $totals['cost'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $this->syncEntries($log, $validated['entries']);
                $log->load(['jobOrder.customer', 'jobOrder.jobSite', 'entries']);

                return $log;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($log),
                'message' => 'Daily work log created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create daily work log',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $log = JobOrderDailyWorkLog::with(['jobOrder.customer', 'jobOrder.jobSite', 'entries'])->find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily work log not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($log),
                'message' => 'Daily work log retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily work log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateJobOrderDailyWorkLogRequest $request, int $id): JsonResponse
    {
        try {
            $log = JobOrderDailyWorkLog::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily work log not found',
                ], 404);
            }

            $validated = $request->validated();

            $log = DB::transaction(function () use ($log, $validated) {
                $totals = $this->calculateTotals($validated['entries']);
                $log->update([
                    'job_order_id' => $validated['job_order_id'],
                    'period_start' => $validated['period_start'],
                    'period_end' => $validated['period_end'],
                    'project_manager' => $validated['project_manager'] ?? null,
                    'total_wages' => $totals['wages'],
                    'total_materials' => $totals['materials'],
                    'total_overhead' => $totals['overhead'],
                    'total_cost' => $totals['cost'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $log->entries()->delete();
                $this->syncEntries($log, $validated['entries']);
                $log->load(['jobOrder.customer', 'jobOrder.jobSite', 'entries']);

                return $log;
            });

            return response()->json([
                'success' => true,
                'data' => $this->transformLog($log),
                'message' => 'Daily work log updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update daily work log',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $log = JobOrderDailyWorkLog::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily work log not found',
                ], 404);
            }

            $log->entries()->delete();
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Daily work log deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete daily work log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function jobOrders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => JobOrder::with(['customer', 'jobSite'])
                ->latest()
                ->get()
                ->map(function (JobOrder $jobOrder) {
                    return [
                        'id' => $jobOrder->id,
                        'job_order_no' => $jobOrder->job_order_no,
                        'job_description' => $jobOrder->job_description,
                        'customer_name' => $jobOrder->customer?->company_name ?: $jobOrder->customer?->account_name,
                        'job_site_address' => $jobOrder->jobSite?->address,
                        'budget' => (float) $jobOrder->total,
                        'start_date' => $jobOrder->schedule_start_date?->format('Y-m-d H:i:s'),
                        'end_date' => $jobOrder->schedule_end_date?->format('Y-m-d H:i:s'),
                        'project_manager' => $jobOrder->site_contact_person,
                    ];
                }),
        ]);
    }

    private function syncEntries(JobOrderDailyWorkLog $log, array $entries): void
    {
        $runningTotal = 0;

        foreach ($entries as $entry) {
            $dailyTotal =
                (float) ($entry['wages'] ?? 0) +
                (float) ($entry['materials'] ?? 0) +
                (float) ($entry['overhead'] ?? 0);
            $runningTotal += $dailyTotal;

            JobOrderDailyWorkLogEntry::create([
                'daily_work_log_id' => $log->id,
                'work_date' => $entry['work_date'],
                'day_name' => Carbon::parse($entry['work_date'])->format('D'),
                'start_time' => $entry['start_time'] ?? null,
                'end_time' => $entry['end_time'] ?? null,
                'activity_description' => $entry['activity_description'] ?? null,
                'wages' => $entry['wages'] ?? 0,
                'materials' => $entry['materials'] ?? 0,
                'overhead' => $entry['overhead'] ?? 0,
                'daily_total' => $dailyTotal,
                'running_total' => $runningTotal,
            ]);
        }
    }

    private function calculateTotals(array $entries): array
    {
        $wages = collect($entries)->sum(fn (array $entry) => (float) ($entry['wages'] ?? 0));
        $materials = collect($entries)->sum(fn (array $entry) => (float) ($entry['materials'] ?? 0));
        $overhead = collect($entries)->sum(fn (array $entry) => (float) ($entry['overhead'] ?? 0));

        return [
            'wages' => $wages,
            'materials' => $materials,
            'overhead' => $overhead,
            'cost' => $wages + $materials + $overhead,
        ];
    }

    private function generateLogNo(): string
    {
        $year = date('Y');
        $latest = JobOrderDailyWorkLog::where('log_no', 'like', "DWL-{$year}-%")
            ->orderBy('log_no', 'desc')
            ->first();

        $next = $latest ? ((int) substr($latest->log_no, -4)) + 1 : 1;

        return sprintf('DWL-%s-%04d', $year, $next);
    }

    private function transformLog(JobOrderDailyWorkLog $log): array
    {
        $jobOrder = $log->jobOrder;
        $remaining = $jobOrder && $jobOrder->total
            ? max((float) $jobOrder->total - (float) $log->total_cost, 0)
            : 0;

        return [
            'id' => $log->id,
            'log_no' => $log->log_no,
            'job_order_id' => $log->job_order_id,
            'job_order' => $jobOrder ? [
                'id' => $jobOrder->id,
                'job_order_no' => $jobOrder->job_order_no,
                'job_description' => $jobOrder->job_description,
                'budget' => (float) $jobOrder->total,
                'customer_name' => $jobOrder->customer?->company_name ?: $jobOrder->customer?->account_name,
                'job_site_address' => $jobOrder->jobSite?->address,
                'project_manager' => $log->project_manager ?: $jobOrder->site_contact_person,
                'start_date' => $jobOrder->schedule_start_date?->format('Y-m-d H:i:s'),
                'end_date' => $jobOrder->schedule_end_date?->format('Y-m-d H:i:s'),
            ] : null,
            'period_start' => $log->period_start?->format('Y-m-d'),
            'period_end' => $log->period_end?->format('Y-m-d'),
            'project_manager' => $log->project_manager,
            'total_wages' => (float) $log->total_wages,
            'total_materials' => (float) $log->total_materials,
            'total_overhead' => (float) $log->total_overhead,
            'total_cost' => (float) $log->total_cost,
            'balance' => $remaining,
            'notes' => $log->notes,
            'entries' => $log->entries
                ->sortBy('work_date')
                ->values()
                ->map(function (JobOrderDailyWorkLogEntry $entry) {
                    return [
                        'id' => $entry->id,
                        'work_date' => $entry->work_date?->format('Y-m-d'),
                        'day_name' => $entry->day_name,
                        'start_time' => $entry->start_time,
                        'end_time' => $entry->end_time,
                        'activity_description' => $entry->activity_description,
                        'wages' => (float) $entry->wages,
                        'materials' => (float) $entry->materials,
                        'overhead' => (float) $entry->overhead,
                        'daily_total' => (float) $entry->daily_total,
                        'running_total' => (float) $entry->running_total,
                    ];
                }),
            'created_at' => $log->created_at?->toIso8601String(),
            'updated_at' => $log->updated_at?->toIso8601String(),
        ];
    }
}
