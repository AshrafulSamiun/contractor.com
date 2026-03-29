<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkforceApproval;
use App\Models\WorkforceDailyReport;
use App\Models\WorkforceIncidentReport;
use App\Models\WorkforceTimesheet;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Http\Request;

class WorkforceApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'entity_type' => ['required', 'string', 'max:40'],
            'entity_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $companyUserIds = $this->companyUserIds($request);
        $items = WorkforceApproval::query()
            ->where('entity_type', $request->string('entity_type')->toString())
            ->where('entity_id', (int) $request->integer('entity_id'))
            ->whereIn('user_id', $companyUserIds)
            ->when(($user->role ?? null) !== 'admin', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function export(Request $request)
    {
        if (!app(PermissionService::class)->can($request->user(), 'workforce', 'export')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $companyUserIds = $this->companyUserIds($request);
        $query = WorkforceApproval::query()
            ->whereIn('user_id', $companyUserIds)
            ->orderByDesc('created_at');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type')->toString());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        $filename = 'approval_audit_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id',
                'entity_type',
                'entity_id',
                'status',
                'step',
                'user_id',
                'approved_by',
                'actor_role',
                'approved_at',
                'note',
                'created_at',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->entity_type,
                        $row->entity_id,
                        $row->status,
                        $row->step,
                        $row->user_id,
                        $row->approved_by,
                        $row->actor_role,
                        optional($row->approved_at)->toDateTimeString(),
                        $row->note,
                        optional($row->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function slaDashboard(Request $request)
    {
        if (!app(PermissionService::class)->can($request->user(), 'workforce', 'read')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $companyUserIds = $this->companyUserIds($request);
        $flow = app(WorkforceApprovalFlow::class);
        $modules = [
            'daily_report' => WorkforceDailyReport::class,
            'incident_report' => WorkforceIncidentReport::class,
            'timesheet' => WorkforceTimesheet::class,
        ];
        $selectedModule = $request->string('module')->toString();
        if ($selectedModule && isset($modules[$selectedModule])) {
            $modules = [$selectedModule => $modules[$selectedModule]];
        }
        $status = $request->string('status')->toString();
        $allowedStatuses = ['submitted', 'approved'];
        $statusFilter = in_array($status, $allowedStatuses, true) ? [$status] : $allowedStatuses;
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();
        $minAge = (int) $request->integer('min_age_hours');

        $summary = [];
        $items = [];

        foreach ($modules as $key => $model) {
            $slaHours = max(1, $flow->getSlaHours($key));
            $threshold = $minAge > 0 ? max($slaHours, $minAge) : $slaHours;
            $cutoff = now()->subHours($threshold);

            $query = $model::query()
                ->whereIn('user_id', $companyUserIds)
                ->whereIn('status', $statusFilter)
                ->where('updated_at', '<=', $cutoff);
            if ($from) {
                $query->whereDate('updated_at', '>=', $from);
            }
            if ($to) {
                $query->whereDate('updated_at', '<=', $to);
            }
            $pending = $query->get();

            $summary[$key] = [
                'sla_hours' => $slaHours,
                'overdue' => $pending->count(),
            ];

            foreach ($pending as $item) {
                $items[] = [
                    'module' => $key,
                    'id' => $item->id,
                    'status' => $item->status,
                    'updated_at' => optional($item->updated_at)->toDateTimeString(),
                    'reference' => $item->report_no ?? $item->incident_no ?? $item->timesheet_no ?? null,
                    'age_hours' => optional($item->updated_at)->diffInHours(now()),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'items' => $items,
            ],
        ]);
    }

    public function exportSla(Request $request)
    {
        if (!app(PermissionService::class)->can($request->user(), 'workforce', 'export')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $companyUserIds = $this->companyUserIds($request);
        $flow = app(WorkforceApprovalFlow::class);
        $modules = [
            'daily_report' => WorkforceDailyReport::class,
            'incident_report' => WorkforceIncidentReport::class,
            'timesheet' => WorkforceTimesheet::class,
        ];
        $selectedModule = $request->string('module')->toString();
        if ($selectedModule && isset($modules[$selectedModule])) {
            $modules = [$selectedModule => $modules[$selectedModule]];
        }
        $status = $request->string('status')->toString();
        $allowedStatuses = ['submitted', 'approved'];
        $statusFilter = in_array($status, $allowedStatuses, true) ? [$status] : $allowedStatuses;
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();
        $minAge = (int) $request->integer('min_age_hours');

        $rows = [];
        foreach ($modules as $key => $model) {
            $slaHours = max(1, $flow->getSlaHours($key));
            $threshold = $minAge > 0 ? max($slaHours, $minAge) : $slaHours;
            $cutoff = now()->subHours($threshold);
            $query = $model::query()
                ->whereIn('user_id', $companyUserIds)
                ->whereIn('status', $statusFilter)
                ->where('updated_at', '<=', $cutoff);
            if ($from) {
                $query->whereDate('updated_at', '>=', $from);
            }
            if ($to) {
                $query->whereDate('updated_at', '<=', $to);
            }
            $pending = $query->get();

            foreach ($pending as $item) {
                $rows[] = [
                    'module' => $key,
                    'reference' => $item->report_no ?? $item->incident_no ?? $item->timesheet_no ?? null,
                    'status' => $item->status,
                    'age_hours' => optional($item->updated_at)->diffInHours(now()),
                    'updated_at' => optional($item->updated_at)->toDateTimeString(),
                    'sla_hours' => $slaHours,
                ];
            }
        }

        $filename = 'approval_sla_breaches_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['module', 'reference', 'status', 'age_hours', 'updated_at', 'sla_hours']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function companyUserIds(Request $request)
    {
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName === '') {
            return collect([$request->user()->id]);
        }

        return User::query()
            ->where('company_name', $companyName)
            ->pluck('id');
    }
}
