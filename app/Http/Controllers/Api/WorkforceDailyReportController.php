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
        $query = WorkforceDailyReport::query()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('from')) {
            $query->whereDate('report_date', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('report_date', '<=', $request->string('to')->toString());
        }

        $items = $query->orderByDesc('report_date')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, WorkforceDailyReport $report)
    {
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $report]);
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
            'expire_date' => ['nullable', 'date'],
            'licence_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'metrics_json' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;

        $report = WorkforceDailyReport::create($data);
        if (!$report->report_no) {
            $report->report_no = sprintf('DR-%s-%05d', now()->format('Y'), $report->id);
            $report->save();
        }

        return response()->json(['success' => true, 'data' => $report], 201);
    }

    public function update(Request $request, WorkforceDailyReport $report)
    {
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:40'],
            'shift_name' => ['nullable', 'string', 'max:60'],
            'shift_time' => ['nullable', 'date_format:H:i'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'report_notes' => ['nullable', 'string', 'max:5000'],
            'report_location' => ['nullable', 'string', 'max:120'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'expire_date' => ['nullable', 'date'],
            'licence_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'metrics_json' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $report->update($data);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function destroy(Request $request, WorkforceDailyReport $report)
    {
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report->delete();

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, WorkforceDailyReport $report)
    {
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

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
        $this->assertSameCompany($request, $report->user_id);

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
        $this->assertSameCompany($request, $report->user_id);

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
        $this->assertSameCompany($request, $report->user_id);

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
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName !== '') {
            $query->where('company_name', $companyName);
        } else {
            $query->whereKey($request->user()->id);
        }

        return $query->get();
    }

    private function assertSameCompany(Request $request, int $ownerUserId): void
    {
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName === '') {
            if ($request->user()->id !== $ownerUserId) {
                abort(403, 'Unauthorized');
            }
            return;
        }

        $ownerCompany = trim((string) User::query()->whereKey($ownerUserId)->value('company_name'));
        if ($ownerCompany === '' || $ownerCompany !== $companyName) {
            abort(403, 'Unauthorized');
        }
    }
}
