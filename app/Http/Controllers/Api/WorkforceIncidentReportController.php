<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkforceApproval;
use App\Models\WorkforceIncidentReport;
use App\Services\NotificationDispatcher;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Http\Request;

class WorkforceIncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkforceIncidentReport::query()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity')->toString());
        }

        $items = $query->orderByDesc('occurred_at')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, WorkforceIncidentReport $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $incident]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'jobsite' => ['nullable', 'string', 'max:120'],
            'shift_name' => ['nullable', 'string', 'max:60'],
            'severity' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'actions_taken' => ['nullable', 'string', 'max:5000'],
            'incident_notes' => ['nullable', 'string', 'max:5000'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'expire_date' => ['nullable', 'date'],
            'license_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'incident_type' => ['nullable', 'string', 'max:80'],
            'incident_categories' => ['nullable', 'array'],
            'incident_description' => ['nullable', 'string', 'max:5000'],
            'incident_damages' => ['nullable', 'string', 'max:5000'],
            'incident_injuries' => ['nullable', 'string', 'max:5000'],
            'action_taken' => ['nullable', 'string', 'max:5000'],
            'involved_people' => ['nullable', 'array'],
            'witness_people' => ['nullable', 'array'],
            'police_called' => ['nullable', 'boolean'],
            'police_file_no' => ['nullable', 'string', 'max:80'],
            'police_officer_name' => ['nullable', 'string', 'max:120'],
            'badge_no' => ['nullable', 'string', 'max:80'],
            'fire_dept_called' => ['nullable', 'boolean'],
            'ambulance_called' => ['nullable', 'boolean'],
            'emergency_details' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;

        $incident = WorkforceIncidentReport::create($data);
        if (!$incident->incident_no) {
            $incident->incident_no = sprintf('IR-%s-%05d', now()->format('Y'), $incident->id);
            $incident->save();
        }

        return response()->json(['success' => true, 'data' => $incident], 201);
    }

    public function update(Request $request, WorkforceIncidentReport $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'jobsite' => ['nullable', 'string', 'max:120'],
            'shift_name' => ['nullable', 'string', 'max:60'],
            'severity' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'actions_taken' => ['nullable', 'string', 'max:5000'],
            'incident_notes' => ['nullable', 'string', 'max:5000'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'expire_date' => ['nullable', 'date'],
            'license_no' => ['nullable', 'string', 'max:60'],
            'is_valid' => ['nullable', 'boolean'],
            'incident_type' => ['nullable', 'string', 'max:80'],
            'incident_categories' => ['nullable', 'array'],
            'incident_description' => ['nullable', 'string', 'max:5000'],
            'incident_damages' => ['nullable', 'string', 'max:5000'],
            'incident_injuries' => ['nullable', 'string', 'max:5000'],
            'action_taken' => ['nullable', 'string', 'max:5000'],
            'involved_people' => ['nullable', 'array'],
            'witness_people' => ['nullable', 'array'],
            'police_called' => ['nullable', 'boolean'],
            'police_file_no' => ['nullable', 'string', 'max:80'],
            'police_officer_name' => ['nullable', 'string', 'max:120'],
            'badge_no' => ['nullable', 'string', 'max:80'],
            'fire_dept_called' => ['nullable', 'boolean'],
            'ambulance_called' => ['nullable', 'boolean'],
            'emergency_details' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'max:20'],
        ]);

        $incident->update($data);

        return response()->json(['success' => true, 'data' => $incident]);
    }

    public function destroy(Request $request, WorkforceIncidentReport $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $incident->delete();

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, WorkforceIncidentReport $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'submit', 'incident_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $incident->update(['status' => 'submitted']);
        WorkforceApproval::create([
            'user_id' => $incident->user_id,
            'entity_type' => 'incident_report',
            'entity_id' => $incident->id,
            'status' => 'submitted',
            'step' => 'submit',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $roles = $flow->rolesFor('approve', 'incident_report');
        $approvers = $this->approversForRoles($request, $roles);
        foreach ($approvers as $approver) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                'subject' => 'Incident Report submitted',
                'message' => "An incident report ({$incident->incident_no}) is ready for approval.",
                'entity_type' => 'incident_report',
                'entity_id' => $incident->id,
                'status' => 'submitted',
            ], 'incident_report_submitted');
        }

        return response()->json(['success' => true, 'data' => $incident]);
    }

    public function approve(Request $request, WorkforceIncidentReport $incident)
    {
        $this->assertSameCompany($request, $incident->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'incident_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $incident->update(['status' => 'approved']);
        WorkforceApproval::create([
            'user_id' => $incident->user_id,
            'entity_type' => 'incident_report',
            'entity_id' => $incident->id,
            'status' => 'approved',
            'step' => 'approve',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($incident->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Incident Report approved',
                'message' => "Your incident report ({$incident->incident_no}) was approved.",
                'entity_type' => 'incident_report',
                'entity_id' => $incident->id,
                'status' => 'approved',
            ], 'incident_report_approved');
        }

        if ($flow->isEnabled('final', 'incident_report')) {
            $roles = $flow->rolesFor('final', 'incident_report');
            $approvers = $this->approversForRoles($request, $roles);
            foreach ($approvers as $approver) {
                app(NotificationDispatcher::class)->sendWorkforceEvent($approver, [
                    'subject' => 'Incident Report ready for final approval',
                    'message' => "An incident report ({$incident->incident_no}) is ready for final approval.",
                    'entity_type' => 'incident_report',
                    'entity_id' => $incident->id,
                    'status' => 'approved',
                ], 'incident_report_final_ready');
            }
        }

        return response()->json(['success' => true, 'data' => $incident]);
    }

    public function finalize(Request $request, WorkforceIncidentReport $incident)
    {
        $this->assertSameCompany($request, $incident->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'final', 'incident_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $incident->update(['status' => 'final']);
        WorkforceApproval::create([
            'user_id' => $incident->user_id,
            'entity_type' => 'incident_report',
            'entity_id' => $incident->id,
            'status' => 'final',
            'step' => 'final',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($incident->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Incident Report finalized',
                'message' => "Your incident report ({$incident->incident_no}) is finalized.",
                'entity_type' => 'incident_report',
                'entity_id' => $incident->id,
                'status' => 'final',
            ], 'incident_report_final');
        }

        return response()->json(['success' => true, 'data' => $incident]);
    }

    public function reject(Request $request, WorkforceIncidentReport $incident)
    {
        $this->assertSameCompany($request, $incident->user_id);

        $flow = app(WorkforceApprovalFlow::class);
        if (!$flow->isRoleAllowed($request->user()->role ?? null, 'approve', 'incident_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $incident->update(['status' => 'rejected']);
        WorkforceApproval::create([
            'user_id' => $incident->user_id,
            'entity_type' => 'incident_report',
            'entity_id' => $incident->id,
            'status' => 'rejected',
            'step' => 'reject',
            'approved_by' => $request->user()->id,
            'actor_role' => $request->user()->role ?? null,
            'approved_at' => now(),
            'note' => $request->input('note'),
        ]);

        $owner = User::find($incident->user_id);
        if ($owner) {
            app(NotificationDispatcher::class)->sendWorkforceEvent($owner, [
                'subject' => 'Incident Report rejected',
                'message' => "Your incident report ({$incident->incident_no}) was rejected.",
                'entity_type' => 'incident_report',
                'entity_id' => $incident->id,
                'status' => 'rejected',
            ], 'incident_report_rejected');
        }

        return response()->json(['success' => true, 'data' => $incident]);
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
