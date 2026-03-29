<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkforceApproval;
use App\Models\WorkforceTimesheet;
use App\Services\NotificationDispatcher;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Http\Request;

class WorkforceTimesheetController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkforceTimesheet::query()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('from')) {
            $query->whereDate('week_start', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('week_start', '<=', $request->string('to')->toString());
        }

        $items = $query->orderByDesc('week_start')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, WorkforceTimesheet $timesheet)
    {
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'week_start' => ['required', 'date'],
            'week_end' => ['nullable', 'date'],
            'employee_name' => ['required', 'string', 'max:120'],
            'employee_code' => ['nullable', 'string', 'max:60'],
            'role_title' => ['nullable', 'string', 'max:80'],
            'regular_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
            'entries_json' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['user_id'] = $request->user()->id;
        $totals = $this->calculateTotals($data['entries_json'] ?? []);
        $data['regular_hours'] = $data['regular_hours'] ?? $totals['regular_hours'];
        $data['overtime_hours'] = $data['overtime_hours'] ?? $totals['overtime_hours'];
        $data['total_hours'] = $data['total_hours'] ?? $totals['total_hours'];

        $timesheet = WorkforceTimesheet::create($data);
        if (!$timesheet->timesheet_no) {
            $timesheet->timesheet_no = sprintf('TS-%s-%05d', now()->format('Y'), $timesheet->id);
            $timesheet->save();
        }

        return response()->json(['success' => true, 'data' => $timesheet], 201);
    }

    public function update(Request $request, WorkforceTimesheet $timesheet)
    {
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'week_start' => ['required', 'date'],
            'week_end' => ['nullable', 'date'],
            'employee_name' => ['required', 'string', 'max:120'],
            'employee_code' => ['nullable', 'string', 'max:60'],
            'role_title' => ['nullable', 'string', 'max:80'],
            'regular_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
            'entries_json' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $totals = $this->calculateTotals($data['entries_json'] ?? []);
        $data['regular_hours'] = $data['regular_hours'] ?? $totals['regular_hours'];
        $data['overtime_hours'] = $data['overtime_hours'] ?? $totals['overtime_hours'];
        $data['total_hours'] = $data['total_hours'] ?? $totals['total_hours'];

        $timesheet->update($data);

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    public function submit(Request $request, WorkforceTimesheet $timesheet)
    {
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'submit', 'timesheet')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $timesheet->update(['status' => 'submitted']);
        WorkforceApproval::create([
            'user_id' => $timesheet->user_id,
            'entity_type' => 'timesheet',
            'entity_id' => $timesheet->id,
            'status' => 'submitted',
            'step' => 'submit',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $roles = $flow->rolesFor('approve', 'timesheet');
        $approvers = $this->approversForRoles($request, $roles);
        foreach ($approvers as $approver) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                'subject' => 'Timesheet submitted',
                'message' => "A timesheet ({$timesheet->timesheet_no}) is ready for approval.",
                'entity_type' => 'timesheet',
                'entity_id' => $timesheet->id,
                'status' => 'submitted',
            ], 'timesheet_submitted');
        }

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    public function approve(Request $request, WorkforceTimesheet $timesheet)
    {
        $this->assertSameCompany($request, $timesheet->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'timesheet')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $timesheet->update([
            'status' => 'approved',
            'approved_by' => $request->user()->name ?? 'Admin',
            'approved_at' => now(),
        ]);

        WorkforceApproval::create([
            'user_id' => $timesheet->user_id,
            'entity_type' => 'timesheet',
            'entity_id' => $timesheet->id,
            'status' => 'approved',
            'step' => 'approve',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($timesheet->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Timesheet approved',
                'message' => "Your timesheet ({$timesheet->timesheet_no}) was approved.",
                'entity_type' => 'timesheet',
                'entity_id' => $timesheet->id,
                'status' => 'approved',
            ], 'timesheet_approved');
        }

        if ($flow->isEnabled('final', 'timesheet')) {
            $roles = $flow->rolesFor('final', 'timesheet');
            $approvers = $this->approversForRoles($request, $roles);
            foreach ($approvers as $approver) {
                app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                    'subject' => 'Timesheet ready for final approval',
                    'message' => "A timesheet ({$timesheet->timesheet_no}) is ready for final approval.",
                    'entity_type' => 'timesheet',
                    'entity_id' => $timesheet->id,
                    'status' => 'approved',
                ], 'timesheet_final_ready');
            }
        }

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    public function finalize(Request $request, WorkforceTimesheet $timesheet)
    {
        $this->assertSameCompany($request, $timesheet->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'final', 'timesheet')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $timesheet->update([
            'status' => 'final',
            'approved_by' => $request->user()->name ?? 'Admin',
            'approved_at' => now(),
        ]);

        WorkforceApproval::create([
            'user_id' => $timesheet->user_id,
            'entity_type' => 'timesheet',
            'entity_id' => $timesheet->id,
            'status' => 'final',
            'step' => 'final',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($timesheet->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Timesheet finalized',
                'message' => "Your timesheet ({$timesheet->timesheet_no}) is finalized.",
                'entity_type' => 'timesheet',
                'entity_id' => $timesheet->id,
                'status' => 'final',
            ], 'timesheet_final');
        }

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    public function reject(Request $request, WorkforceTimesheet $timesheet)
    {
        $this->assertSameCompany($request, $timesheet->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'timesheet')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $timesheet->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->name ?? 'Admin',
            'approved_at' => now(),
        ]);

        WorkforceApproval::create([
            'user_id' => $timesheet->user_id,
            'entity_type' => 'timesheet',
            'entity_id' => $timesheet->id,
            'status' => 'rejected',
            'step' => 'reject',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($timesheet->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Timesheet rejected',
                'message' => "Your timesheet ({$timesheet->timesheet_no}) was rejected.",
                'entity_type' => 'timesheet',
                'entity_id' => $timesheet->id,
                'status' => 'rejected',
            ], 'timesheet_rejected');
        }

        return response()->json(['success' => true, 'data' => $timesheet]);
    }

    private function calculateTotals(array $entries): array
    {
        $regular = 0;
        $overtime = 0;
        foreach ($entries as $entry) {
            $regular += (float) ($entry['hours'] ?? 0);
            $overtime += (float) ($entry['overtime'] ?? 0);
        }

        return [
            'regular_hours' => round($regular, 2),
            'overtime_hours' => round($overtime, 2),
            'total_hours' => round($regular + $overtime, 2),
        ];
    }

    public function destroy(Request $request, WorkforceTimesheet $timesheet)
    {
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $timesheet->delete();

        return response()->json(['success' => true]);
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
