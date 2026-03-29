<?php

namespace Tests\Feature;

use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TodoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_assign_task_to_staff(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');
        $outside = $this->makeUser('staff', 'OtherCo');

        Sanctum::actingAs($manager);
        $create = $this->postJson('/api/v1/todo', $this->payload([
            'assignee_user_id' => $staff->id,
        ]));
        $create->assertCreated();
        $taskId = (int) $create->json('data.id');

        $this->assertDatabaseHas('todo_tasks', [
            'id' => $taskId,
            'user_id' => $manager->id,
            'assignee_user_id' => $staff->id,
            'employee_name' => $staff->name,
        ]);

        Sanctum::actingAs($staff);
        $staffList = $this->getJson('/api/v1/todo')->assertOk()->json('data');
        $this->assertCount(1, $staffList);
        $this->assertSame($taskId, $staffList[0]['id']);

        Sanctum::actingAs($outside);
        $outsideList = $this->getJson('/api/v1/todo')->assertOk()->json('data');
        $this->assertCount(0, $outsideList);
    }

    public function test_staff_cannot_create_task(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');

        Sanctum::actingAs($staff);
        $this->postJson('/api/v1/todo', $this->payload([
            'assignee_user_id' => $manager->id,
        ]))->assertStatus(403);
    }

    public function test_assigned_staff_can_update_status_but_not_core_fields(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');

        $task = TodoTask::create([
            'user_id' => $manager->id,
            'assignee_user_id' => $staff->id,
            'task_no' => 'TSK-2026-001',
            'details' => 'Original details for restricted update testing.',
            'employee_name' => $staff->name,
            'due_at' => now()->addDay(),
            'status' => 'Pending',
        ]);

        Sanctum::actingAs($staff);
        $this->putJson('/api/v1/todo/' . $task->id, $this->payload([
            'assignee_user_id' => $manager->id, // should be ignored for staff
            'details' => 'Staff should not overwrite this detail field.',
            'status' => 'Completed',
            'action' => 'Task completed by assigned staff.',
            'action_date' => now()->toDateString(),
        ]))->assertOk();

        $task->refresh();
        $this->assertSame('Original details for restricted update testing.', $task->details);
        $this->assertSame($staff->id, (int) $task->assignee_user_id);
        $this->assertSame('Completed', $task->status);
        $this->assertSame('Task completed by assigned staff.', $task->action);
    }

    public function test_unassigned_staff_cannot_update_task(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $assignee = $this->makeUser('staff', 'OpsCo');
        $otherStaff = $this->makeUser('staff', 'OpsCo');

        $task = TodoTask::create([
            'user_id' => $manager->id,
            'assignee_user_id' => $assignee->id,
            'task_no' => 'TSK-2026-002',
            'details' => 'Task should be locked for unassigned staff update.',
            'employee_name' => $assignee->name,
            'due_at' => now()->addDay(),
            'status' => 'Pending',
        ]);

        Sanctum::actingAs($otherStaff);
        $this->putJson('/api/v1/todo/' . $task->id, $this->payload([
            'assignee_user_id' => $assignee->id,
        ]))->assertStatus(403);
    }

    public function test_manager_can_review_completed_task(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');

        $task = TodoTask::create([
            'user_id' => $manager->id,
            'assignee_user_id' => $staff->id,
            'task_no' => 'TSK-2026-003',
            'details' => 'Completed task for manager review workflow testing.',
            'employee_name' => $staff->name,
            'due_at' => now()->addDay(),
            'status' => 'Completed',
            'action' => 'Completed by staff',
            'action_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($manager);
        $this->postJson('/api/v1/todo/' . $task->id . '/review', [
            'review_note' => 'Looks good. Approved for closure.',
        ])->assertOk();

        $this->assertDatabaseHas('todo_tasks', [
            'id' => $task->id,
            'reviewed_by_user_id' => $manager->id,
            'review_note' => 'Looks good. Approved for closure.',
        ]);
    }

    public function test_assignees_endpoint_returns_only_same_company_users(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');
        $admin = $this->makeUser('admin', 'OpsCo');
        $outside = $this->makeUser('staff', 'OtherCo');
        $inactive = $this->makeUser('staff', 'OpsCo', false);

        Sanctum::actingAs($manager);
        $response = $this->getJson('/api/v1/todo/assignees')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains($staff->id, $ids);
        $this->assertContains($admin->id, $ids);
        $this->assertContains($manager->id, $ids);
        $this->assertNotContains($outside->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_legacy_invalid_reminder_values_do_not_crash_todo_list(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');

        TodoTask::create([
            'user_id' => $manager->id,
            'assignee_user_id' => $staff->id,
            'task_no' => 'TSK-2026-900',
            'details' => 'Legacy reminder text should not break listing endpoint.',
            'employee_name' => $staff->name,
            'due_at' => now()->addDay(),
            'reminder_1' => 'sdd',
            'status' => 'Pending',
        ]);

        Sanctum::actingAs($manager);
        $response = $this->getJson('/api/v1/todo');
        $response->assertOk();
        $this->assertSame('sdd', $response->json('data.0.reminder_1'));
    }

    public function test_assigned_staff_update_does_not_crash_on_legacy_invalid_reminders(): void
    {
        $manager = $this->makeUser('manager', 'OpsCo');
        $staff = $this->makeUser('staff', 'OpsCo');

        $task = TodoTask::create([
            'user_id' => $manager->id,
            'assignee_user_id' => $staff->id,
            'task_no' => 'TSK-2026-901',
            'details' => 'Legacy reminder text should not break assigned staff update flow.',
            'employee_name' => $staff->name,
            'due_at' => now()->addDay(),
            'reminder_1' => 'sdd',
            'status' => 'Pending',
        ]);

        Sanctum::actingAs($staff);
        $this->putJson('/api/v1/todo/' . $task->id, [
            'details' => 'Staff execution update payload.',
            'due_at' => now()->addDay()->toDateTimeString(),
            'status' => 'In Progress',
        ])->assertOk();

        $task->refresh();
        $this->assertNull($task->reminder_1);
        $this->assertSame('In Progress', $task->status);
    }

    private function makeUser(string $role, string $company, bool $active = true): User
    {
        return User::factory()->create([
            'role' => $role,
            'company_name' => $company,
            'is_active' => $active,
            'email_verified_at' => now(),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'details' => 'Task details text that is safely longer than ten chars.',
            'due_at' => now()->addHour()->toDateTimeString(),
            'status' => 'Pending',
        ], $overrides);
    }
}
