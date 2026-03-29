<?php

namespace Tests\Feature;

use App\Models\TodoTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TodoActionValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'company_name' => 'Action Co',
            'is_active' => true,
        ]);
        $this->staff = User::factory()->create([
            'role' => 'staff',
            'company_name' => 'Action Co',
            'is_active' => true,
        ]);
    }

    public function test_completed_status_requires_action_and_action_date(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/todo', $this->basePayload([
            'status' => 'Completed',
            'action' => '',
            'action_date' => '',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['action', 'action_date']);
    }

    public function test_completed_status_rejects_future_action_date(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/todo', $this->basePayload([
            'status' => 'Completed',
            'action' => 'Called resident and confirmed pickup.',
            'action_date' => now()->addDay()->toDateString(),
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['action_date']);
    }

    public function test_completed_status_accepts_action_with_today_date(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/todo', $this->basePayload([
            'status' => 'Completed',
            'action' => 'Delivered package to resident at front desk.',
            'action_date' => now()->toDateString(),
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('todo_tasks', [
            'user_id' => $this->manager->id,
            'assignee_user_id' => $this->staff->id,
            'status' => 'Completed',
        ]);
        $task = TodoTask::query()->where('user_id', $this->manager->id)->latest('id')->firstOrFail();
        $this->assertSame(now()->toDateString(), Carbon::parse((string) $task->action_date)->toDateString());
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'details' => 'Follow up with resident and close task properly.',
            'assignee_user_id' => $this->staff->id,
            'due_at' => now()->addHour()->toDateTimeString(),
            'status' => 'Pending',
        ], $overrides);
    }
}
