<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\TodoReminderLog;
use App\Models\TodoTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TodoReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_due_todo_reminder_once(): void
    {
        Carbon::setTestNow('2026-02-24 12:00:00');

        $user = User::factory()->create([
            'company_name' => 'Reminder Co',
            'selected_plan' => 'enterprise',
            'role' => 'admin',
        ]);

        $task = TodoTask::create([
            'user_id' => $user->id,
            'task_no' => 'TSK-2026-999',
            'details' => 'Follow up with front desk shift lead immediately.',
            'employee_name' => 'Ops Manager',
            'due_at' => Carbon::now()->addHours(2),
            'reminder_1' => Carbon::now()->toDateTimeString(),
            'status' => 'Pending',
        ]);

        Artisan::call('todo:send-reminders');

        $this->assertDatabaseHas('todo_reminder_logs', [
            'todo_task_id' => $task->id,
            'user_id' => $user->id,
            'reminder_slot' => 1,
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user->id,
            'channel' => 'todo',
            'status' => 'sent',
            'context' => 'todo_reminder',
        ]);
        $this->assertSame(1, TodoReminderLog::query()->count());
        $this->assertSame(1, NotificationLog::query()->where('context', 'todo_reminder')->count());

        Artisan::call('todo:send-reminders');

        $this->assertSame(1, TodoReminderLog::query()->count());
        $this->assertSame(1, NotificationLog::query()->where('context', 'todo_reminder')->count());

        Carbon::setTestNow();
    }

    public function test_completed_todo_does_not_send_reminder(): void
    {
        Carbon::setTestNow('2026-02-24 13:00:00');

        $user = User::factory()->create([
            'company_name' => 'Reminder Co',
            'selected_plan' => 'enterprise',
            'role' => 'admin',
        ]);

        TodoTask::create([
            'user_id' => $user->id,
            'task_no' => 'TSK-2026-1000',
            'details' => 'Completed task should not trigger reminder log.',
            'employee_name' => 'Desk Staff',
            'due_at' => Carbon::now()->addHour(),
            'reminder_1' => Carbon::now()->toDateTimeString(),
            'status' => 'Completed',
        ]);

        Artisan::call('todo:send-reminders');

        $this->assertSame(0, TodoReminderLog::query()->count());
        $this->assertSame(0, NotificationLog::query()->where('context', 'todo_reminder')->count());

        Carbon::setTestNow();
    }
}
