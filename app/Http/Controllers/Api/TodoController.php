<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        $query = $this->baseQueryFor($actor);

        if ($request->filled('task_no')) {
            $query->where('task_no', 'like', '%' . $request->task_no . '%');
        }
        if ($request->filled('employee_name')) {
            $term = $request->string('employee_name')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('employee_name', 'like', '%' . $term . '%')
                    ->orWhereHas('assignee', function ($assigneeQuery) use ($term) {
                        $assigneeQuery->where('name', 'like', '%' . $term . '%');
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        $tasks = $query->orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'data' => $tasks]);
    }

    public function assignees(Request $request)
    {
        $actor = $request->user();
        $users = $this->companyUsersQuery($actor)
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'manager', 'staff'])
            ->select(['id', 'name', 'email', 'role'])
            ->orderByRaw("CASE role WHEN 'manager' THEN 1 WHEN 'staff' THEN 2 WHEN 'admin' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show(Request $request, TodoTask $todo)
    {
        $actor = $request->user();
        if (!$this->canAccessTask($actor, $todo)) {
            return $this->forbidden();
        }

        $todo->load(['creator:id,name,role', 'assignee:id,name,role', 'reviewer:id,name,role']);

        return response()->json(['success' => true, 'data' => $todo]);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        if (!$this->hasSupervisorAccess($actor)) {
            return $this->forbidden('Only Admin or Manager can create tasks.');
        }

        $data = $request->validate($this->baseValidationRules($request) + [
            'assignee_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ], $this->validationMessages());

        $assignee = $this->resolveAssignee($actor, (int) $data['assignee_user_id']);
        if (!$assignee) {
            return $this->validationError([
                'assignee_user_id' => ['Selected employee is invalid for your company.'],
            ]);
        }

        $data['user_id'] = $actor->id;
        $data['task_no'] = $this->generateTaskNo();
        $data['employee_name'] = $assignee->name;
        $data = $this->normalizeTemporalFields($data);

        $task = TodoTask::create($data);
        $task->load(['creator:id,name,role', 'assignee:id,name,role', 'reviewer:id,name,role']);

        $this->logAssignmentNotification($task, $actor);

        return response()->json(['success' => true, 'data' => $task], 201);
    }

    public function update(Request $request, TodoTask $todo)
    {
        $actor = $request->user();
        if (!$this->canAccessTask($actor, $todo)) {
            return $this->forbidden();
        }

        $isSupervisor = $this->hasSupervisorAccess($actor);

        $data = $request->validate($this->baseValidationRules($request) + [
            'assignee_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ], $this->validationMessages());

        if ($isSupervisor) {
            $assigneeId = (int) ($data['assignee_user_id'] ?? $todo->assignee_user_id);
            $assignee = $this->resolveAssignee($actor, $assigneeId);
            if (!$assignee) {
                return $this->validationError([
                    'assignee_user_id' => ['Selected employee is invalid for your company.'],
                ]);
            }
            $data['assignee_user_id'] = $assignee->id;
            $data['employee_name'] = $assignee->name;
        } else {
            // Staff can update only work execution fields for assigned tasks.
            if (!$this->isStaffAssignee($actor, $todo)) {
                return $this->forbidden('Only assigned staff can update this task.');
            }

            $safeDueAt = $this->safeParseDateTime($todo->due_at);
            if ($safeDueAt === null) {
                return $this->validationError([
                    'due_at' => ['Existing task due date is invalid. Please ask Manager/Admin to correct this task first.'],
                ]);
            }

            $data['assignee_user_id'] = $todo->assignee_user_id ?: $actor->id;
            $data['employee_name'] = $todo->employee_name ?: $actor->name;
            $data['details'] = $todo->details;
            $data['due_at'] = $safeDueAt;
            $data['reminder_1'] = $this->safeParseDateTime($todo->reminder_1);
            $data['reminder_2'] = $this->safeParseDateTime($todo->reminder_2);
            $data['reminder_3'] = $this->safeParseDateTime($todo->reminder_3);
        }

        $data = $this->normalizeTemporalFields($data);

        if (($data['status'] ?? $todo->status) !== 'Completed') {
            $data['reviewed_by_user_id'] = null;
            $data['reviewed_at'] = null;
            $data['review_note'] = null;
        }

        $todo->update($data);
        $todo->load(['creator:id,name,role', 'assignee:id,name,role', 'reviewer:id,name,role']);

        return response()->json(['success' => true, 'data' => $todo]);
    }

    public function review(Request $request, TodoTask $todo)
    {
        $actor = $request->user();
        if (!$this->hasSupervisorAccess($actor)) {
            return $this->forbidden('Only Admin or Manager can review completed tasks.');
        }
        if (!$this->canAccessTask($actor, $todo)) {
            return $this->forbidden();
        }
        if ($todo->status !== 'Completed') {
            return $this->validationError([
                'status' => ['Only completed tasks can be reviewed.'],
            ]);
        }

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $todo->review_note = trim((string) ($data['review_note'] ?? '')) ?: null;
        $todo->reviewed_by_user_id = $actor->id;
        $todo->reviewed_at = now();
        $todo->save();
        $todo->load(['creator:id,name,role', 'assignee:id,name,role', 'reviewer:id,name,role']);

        NotificationLog::create([
            'user_id' => $todo->assignee_user_id ?: $todo->user_id,
            'channel' => 'todo',
            'status' => 'sent',
            'to' => null,
            'context' => 'todo_reviewed',
            'context_json' => json_encode([
                'task_id' => $todo->id,
                'task_no' => $todo->task_no,
                'reviewed_by' => $actor->name,
                'reviewed_at' => optional($todo->reviewed_at)->toDateTimeString(),
            ]),
            'error' => null,
        ]);

        return response()->json(['success' => true, 'data' => $todo]);
    }

    public function destroy(Request $request, TodoTask $todo)
    {
        $actor = $request->user();
        if (!$this->hasSupervisorAccess($actor)) {
            return $this->forbidden('Only Admin or Manager can delete tasks.');
        }
        if (!$this->canAccessTask($actor, $todo)) {
            return $this->forbidden();
        }

        $todo->delete();

        return response()->json(['success' => true]);
    }

    private function baseValidationRules(Request $request): array
    {
        return [
            'details' => ['required', 'string', 'min:10'],
            'due_at' => ['required', 'date'],
            'reminder_1' => ['nullable', 'date'],
            'reminder_2' => ['nullable', 'date'],
            'reminder_3' => ['nullable', 'date'],
            'action' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $request->input('status') === 'Completed')],
            'action_date' => ['nullable', 'date', 'before_or_equal:today', Rule::requiredIf(fn () => $request->input('status') === 'Completed')],
            'status' => ['required', 'string', Rule::in(['Pending', 'In Progress', 'Completed', 'On Hold'])],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'action.required' => 'Action is required when status is Completed.',
            'action_date.required' => 'Action date is required when status is Completed.',
            'action_date.before_or_equal' => 'Action date cannot be in the future.',
        ];
    }

    private function normalizeTemporalFields(array $data): array
    {
        $data['due_at'] = Carbon::parse($data['due_at'])->toDateTimeString();

        foreach (['reminder_1', 'reminder_2', 'reminder_3'] as $key) {
            if (!empty($data[$key])) {
                $data[$key] = Carbon::parse($data[$key])->toDateTimeString();
            } else {
                $data[$key] = null;
            }
        }

        if (!empty($data['action_date'])) {
            $data['action_date'] = Carbon::parse($data['action_date'])->toDateString();
        } else {
            $data['action_date'] = null;
        }

        return $data;
    }

    private function safeParseDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return null;
        }

        try {
            return Carbon::parse($stringValue)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function baseQueryFor(User $actor)
    {
        $query = TodoTask::query()->with(['creator:id,name,role', 'assignee:id,name,role', 'reviewer:id,name,role']);

        if ($this->hasSupervisorAccess($actor)) {
            $companyIds = $this->companyUserIds($actor);
            $query->where(function ($scope) use ($companyIds) {
                $scope->whereIn('user_id', $companyIds)
                    ->orWhereIn('assignee_user_id', $companyIds);
            });
        } else {
            $query->where(function ($scope) use ($actor) {
                $scope->where('assignee_user_id', $actor->id)
                    ->orWhere('user_id', $actor->id)
                    ->orWhere(function ($legacy) use ($actor) {
                        $legacy->whereNull('assignee_user_id')
                            ->where('employee_name', $actor->name);
                    });
            });
        }

        return $query;
    }

    private function resolveAssignee(User $actor, int $assigneeId): ?User
    {
        if ($assigneeId <= 0) {
            return null;
        }

        return $this->companyUsersQuery($actor)
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'manager', 'staff'])
            ->whereKey($assigneeId)
            ->first();
    }

    private function companyUsersQuery(User $actor)
    {
        $companyName = trim((string) $actor->company_name);

        if ($companyName === '') {
            return User::query()->whereKey($actor->id);
        }

        return User::query()->where('company_name', $companyName);
    }

    private function companyUserIds(User $actor): Collection
    {
        return $this->companyUsersQuery($actor)->pluck('id');
    }

    private function canAccessTask(User $actor, TodoTask $todo): bool
    {
        if ($this->hasSupervisorAccess($actor)) {
            $ids = $this->companyUserIds($actor);
            return $ids->contains((int) $todo->user_id) || ($todo->assignee_user_id !== null && $ids->contains((int) $todo->assignee_user_id));
        }

        return $this->isStaffAssignee($actor, $todo) || (int) $todo->user_id === (int) $actor->id;
    }

    private function isStaffAssignee(User $actor, TodoTask $todo): bool
    {
        if ((int) $todo->assignee_user_id === (int) $actor->id) {
            return true;
        }

        if ($todo->assignee_user_id === null && trim((string) $todo->employee_name) !== '') {
            return trim((string) $todo->employee_name) === trim((string) $actor->name);
        }

        return false;
    }

    private function isSupervisor(User $user): bool
    {
        return in_array(strtolower((string) $user->role), ['admin', 'manager'], true);
    }

    private function hasSupervisorAccess(User $user): bool
    {
        if ((bool) config('permissions.todo_open_access', false)) {
            return true;
        }

        return $this->isSupervisor($user);
    }

    private function forbidden(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $errors,
        ], 422);
    }

    private function logAssignmentNotification(TodoTask $task, User $actor): void
    {
        if (!$task->assignee_user_id || (int) $task->assignee_user_id === (int) $actor->id) {
            return;
        }

        NotificationLog::create([
            'user_id' => $task->assignee_user_id,
            'channel' => 'todo',
            'status' => 'sent',
            'to' => null,
            'context' => 'todo_assigned',
            'context_json' => json_encode([
                'task_id' => $task->id,
                'task_no' => $task->task_no,
                'assigned_by' => $actor->name,
                'due_at' => optional($task->due_at)->toDateTimeString(),
            ]),
            'error' => null,
        ]);
    }

    private function generateTaskNo(): string
    {
        $prefix = 'TSK-' . Carbon::now()->format('Y');
        $latest = TodoTask::where('task_no', 'like', $prefix . '%')->orderByDesc('id')->first();
        $next = 1;
        if ($latest && preg_match('/TSK-\d{4}-(\d+)/', $latest->task_no, $matches)) {
            $next = (int) $matches[1] + 1;
        }
        return sprintf('%s-%03d', $prefix, $next);
    }
}
