<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkforceApproval;
use App\Models\WorkforceDailyReport;
use App\Services\NotificationDispatcher;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Http\Request;

class WorkforceDailyReportController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->projectScopedReports($request);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('from') || $request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->input('from', $request->input('date_from')));
        }
        if ($request->filled('to') || $request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->input('to', $request->input('date_to')));
        }
        if ($request->filled('report_no')) {
            $query->where('report_no', 'like', '%' . $request->string('report_no')->toString() . '%');
        }
        if ($request->filled('staff')) {
            $query->where('employee_name', 'like', '%' . $request->string('staff')->toString() . '%');
        }
        if ($request->filled('job_order')) {
            $query->where('details_json', 'like', '%' . $request->string('job_order')->toString() . '%');
        }
        if ($request->filled('customer')) {
            $query->where('details_json', 'like', '%' . $request->string('customer')->toString() . '%');
        }
        if ($request->filled('search')) {
            $search = '%' . $request->string('search')->toString() . '%';
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('report_no', 'like', $search)
                    ->orWhere('employee_name', 'like', $search)
                    ->orWhere('report_location', 'like', $search)
                    ->orWhere('shift_name', 'like', $search)
                    ->orWhere('status', 'like', $search);
            });
        }

        $sortBy = $request->string('sort_by')->toString() ?: 'report_date';
        $sortDirection = strtolower($request->string('sort_direction')->toString() ?: 'desc') === 'asc'
            ? 'asc'
            : 'desc';
        $sortableColumns = ['report_no', 'report_date', 'employee_name', 'status'];

        if (in_array($sortBy, $sortableColumns, true)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderByDesc('report_date');
        }

        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $reports = $query->paginate($perPage)->withQueryString();
        $items = $reports->getCollection()->map(function (WorkforceDailyReport $report) {
            return $this->transformReport($report);
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ],
        ]);
    }

    private function transformReport(WorkforceDailyReport $report): array
    {
        $metrics = is_array($report->metrics_json) ? $report->metrics_json : [];
        $regularHours = $this->toDecimalValue($metrics['regular_hours'] ?? null);
        $overtimeHours = $this->toDecimalValue($metrics['overtime_hours'] ?? null);
        $totalHours = $this->toDecimalValue($metrics['total_hours'] ?? null);
        $totalJobs = $metrics['total_jobs']
            ?? (($metrics['jobs'] ?? null) ?? (($metrics['deliveries'] ?? 0) + ($metrics['pickups'] ?? 0)));

        if ($totalHours === null && ($regularHours !== null || $overtimeHours !== null)) {
            $totalHours = (float) ($regularHours ?? 0) + (float) ($overtimeHours ?? 0);
        }

        $details = is_array($report->details_json) ? $report->details_json : [];
        $entries = array_is_list($details) ? $details : ($details['entries'] ?? []);

        return [
            'id' => $report->id,
            'project_id' => $report->project_id,
            'report_no' => $report->report_no,
            'report_date' => optional($report->report_date)->format('Y-m-d'),
            'shift' => $report->shift,
            'shift_name' => $report->shift_name,
            'shift_time' => $report->shift_time,
            'employee_name' => $report->employee_name,
            'employee_code' => $report->employee_code,
            'employee_phone' => $report->employee_phone,
            'employee_email' => $report->employee_email,
            'report_location' => $report->report_location,
            'status' => $report->status,
            'report_notes' => $report->report_notes,
            'licence_no' => $report->licence_no,
            'expire_date' => optional($report->expire_date)->format('Y-m-d'),
            'is_valid' => $report->is_valid,
            'worked_on_stat_holiday' => $report->worked_on_stat_holiday,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'total_hours' => $totalHours,
            'total_jobs' => is_numeric($totalJobs) ? (int) $totalJobs : 0,
            'summary' => $report->summary,
            'metrics_json' => $metrics,
            'department' => $details['department'] ?? null,
            'details_json' => $entries,
        ];
    }

    private function toDecimalValue($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    public function show(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        return response()->json(['success' => true, 'data' => $this->transformReport($report)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:40'],
            'shift_name' => ['nullable', 'string', 'max:60'],
            'shift_time' => ['nullable', 'date_format:H:i'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'report_notes' => ['nullable', 'string', 'max:5000'],
            'report_location' => ['nullable', 'string', 'max:120'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'employee_code' => ['nullable', 'string', 'max:60'],
            'employee_phone' => ['nullable', 'string', 'max:40'],
            'employee_email' => ['nullable', 'email', 'max:120'],
            'expire_date' => ['nullable', 'date'],
            'licence_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'worked_on_stat_holiday' => ['nullable', 'boolean'],
            'metrics_json' => ['nullable', 'array'],
            'details_json' => ['nullable', 'array'],
            'details_json.department' => ['nullable', 'string', 'max:120'],
            'details_json.entries' => ['nullable', 'array'],
            'details_json.entries.*.date' => ['nullable', 'date'],
            'details_json.entries.*.to_date' => ['nullable', 'date'],
            'details_json.entries.*.time_from' => ['nullable', 'date_format:H:i'],
            'details_json.entries.*.time_to' => ['nullable', 'date_format:H:i'],
            'details_json.entries.*.description' => ['nullable', 'string', 'max:5000'],
            'details_json.entries.*.job_order' => ['nullable', 'string', 'max:120'],
            'details_json.entries.*.customer' => ['nullable', 'string', 'max:120'],
            'details_json.*.date' => ['nullable', 'date'],
            'details_json.*.day' => ['nullable', 'string', 'max:30'],
            'details_json.*.time_from' => ['nullable', 'date_format:H:i'],
            'details_json.*.time_to' => ['nullable', 'date_format:H:i'],
            'details_json.*.net_hours' => ['nullable', 'numeric', 'min:0'],
            'details_json.*.overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'details_json.*.customer' => ['nullable', 'string', 'max:120'],
            'details_json.*.job_site' => ['nullable', 'string', 'max:255'],
            'details_json.*.job_order' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['project_id'] = $request->user()->project_id;

        $report = WorkforceDailyReport::create($data);
        if (!$report->report_no) {
            $report->report_no = sprintf('DR-%s-%05d', now()->format('Y'), $report->id);
            $report->save();
        }

        return response()->json(['success' => true, 'data' => $this->transformReport($report)], 201);
    }

    public function update(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:40'],
            'shift_name' => ['nullable', 'string', 'max:60'],
            'shift_time' => ['nullable', 'date_format:H:i'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'report_notes' => ['nullable', 'string', 'max:5000'],
            'report_location' => ['nullable', 'string', 'max:120'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'employee_code' => ['nullable', 'string', 'max:60'],
            'employee_phone' => ['nullable', 'string', 'max:40'],
            'employee_email' => ['nullable', 'email', 'max:120'],
            'expire_date' => ['nullable', 'date'],
            'licence_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'worked_on_stat_holiday' => ['nullable', 'boolean'],
            'metrics_json' => ['nullable', 'array'],
            'details_json' => ['nullable', 'array'],
            'details_json.department' => ['nullable', 'string', 'max:120'],
            'details_json.entries' => ['nullable', 'array'],
            'details_json.entries.*.date' => ['nullable', 'date'],
            'details_json.entries.*.to_date' => ['nullable', 'date'],
            'details_json.entries.*.time_from' => ['nullable', 'date_format:H:i'],
            'details_json.entries.*.time_to' => ['nullable', 'date_format:H:i'],
            'details_json.entries.*.description' => ['nullable', 'string', 'max:5000'],
            'details_json.entries.*.job_order' => ['nullable', 'string', 'max:120'],
            'details_json.entries.*.customer' => ['nullable', 'string', 'max:120'],
            'details_json.*.date' => ['nullable', 'date'],
            'details_json.*.day' => ['nullable', 'string', 'max:30'],
            'details_json.*.time_from' => ['nullable', 'date_format:H:i'],
            'details_json.*.time_to' => ['nullable', 'date_format:H:i'],
            'details_json.*.net_hours' => ['nullable', 'numeric', 'min:0'],
            'details_json.*.overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'details_json.*.customer' => ['nullable', 'string', 'max:120'],
            'details_json.*.job_site' => ['nullable', 'string', 'max:255'],
            'details_json.*.job_order' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $report->update($data);

        return response()->json(['success' => true, 'data' => $this->transformReport($report)]);
    }

    public function destroy(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $report->delete();

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'submit', 'daily_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->update(['status' => 'submitted']);
        WorkforceApproval::create([
            'user_id' => $report->user_id,
            'entity_type' => 'daily_report',
            'entity_id' => $report->id,
            'status' => 'submitted',
            'step' => 'submit',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $roles = $flow->rolesFor('approve', 'daily_report');
        $approvers = $this->approversForRoles($request, $roles);
        foreach ($approvers as $approver) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                'subject' => 'Daily Report submitted',
                'message' => "A daily report ({$report->report_no}) is ready for approval.",
                'entity_type' => 'daily_report',
                'entity_id' => $report->id,
                'status' => 'submitted',
            ], 'daily_report_submitted');
        }

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function approve(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'daily_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->update(['status' => 'approved']);
        WorkforceApproval::create([
            'user_id' => $report->user_id,
            'entity_type' => 'daily_report',
            'entity_id' => $report->id,
            'status' => 'approved',
            'step' => 'approve',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($report->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Daily Report approved',
                'message' => "Your daily report ({$report->report_no}) was approved.",
                'entity_type' => 'daily_report',
                'entity_id' => $report->id,
                'status' => 'approved',
            ], 'daily_report_approved');
        }

        if ($flow->isEnabled('final', 'daily_report')) {
            $roles = $flow->rolesFor('final', 'daily_report');
            $approvers = $this->approversForRoles($request, $roles);
            foreach ($approvers as $approver) {
                app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                    'subject' => 'Daily Report ready for final approval',
                    'message' => "A daily report ({$report->report_no}) is ready for final approval.",
                    'entity_type' => 'daily_report',
                    'entity_id' => $report->id,
                    'status' => 'approved',
                ], 'daily_report_final_ready');
            }
        }

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function finalize(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'final', 'daily_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->update(['status' => 'final']);
        WorkforceApproval::create([
            'user_id' => $report->user_id,
            'entity_type' => 'daily_report',
            'entity_id' => $report->id,
            'status' => 'final',
            'step' => 'final',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($report->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Daily Report finalized',
                'message' => "Your daily report ({$report->report_no}) is finalized.",
                'entity_type' => 'daily_report',
                'entity_id' => $report->id,
                'status' => 'final',
            ], 'daily_report_final');
        }

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function reject(Request $request, WorkforceDailyReport $report)
    {
        $this->assertSameProject($request, $report);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'daily_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->update(['status' => 'rejected']);
        WorkforceApproval::create([
            'user_id' => $report->user_id,
            'entity_type' => 'daily_report',
            'entity_id' => $report->id,
            'status' => 'rejected',
            'step' => 'reject',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($report->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Daily Report rejected',
                'message' => "Your daily report ({$report->report_no}) was rejected.",
                'entity_type' => 'daily_report',
                'entity_id' => $report->id,
                'status' => 'rejected',
            ], 'daily_report_rejected');
        }

        return response()->json(['success' => true, 'data' => $report]);
    }

    private function approversForRoles(Request $request, array $roles)
    {
        $query = User::query()->whereIn('role', $roles);
        if ($request->user()->project_id !== null) {
            $query->where('project_id', $request->user()->project_id);
        } else {
            $query->whereNull('project_id');
        }
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName !== '') {
            $query->where('company_name', $companyName);
        } else {
            $query->whereKey($request->user()->id);
        }

        return $query->get();
    }

    private function projectScopedReports(Request $request)
    {
        $query = WorkforceDailyReport::query();
        if ($request->user()->project_id !== null) {
            $query->where('project_id', $request->user()->project_id);
        } else {
            $query->whereNull('project_id');
        }

        return $query;
    }

    private function assertSameProject(Request $request, WorkforceDailyReport $report): void
    {
        $userProjectId = $request->user()->project_id;
        $reportProjectId = $report->project_id;

        if ($userProjectId === null && $reportProjectId === null) {
            return;
        }

        if ((string) $userProjectId !== (string) $reportProjectId) {
            abort(403, 'Unauthorized');
        }
    }
}
