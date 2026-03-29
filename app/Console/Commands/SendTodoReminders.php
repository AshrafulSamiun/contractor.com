<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\TodoReminderLog;
use App\Models\TodoTask;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTodoReminders extends Command
{
    protected $signature = 'todo:send-reminders';
    protected $description = 'Send To-Do reminders into notification logs.';

    public function handle(): int
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subMinute();
        $windowEnd = $now->copy()->addMinute();

        $tasks = TodoTask::query()
            ->where(function ($query) {
                $query->whereNotNull('reminder_1')
                    ->orWhereNotNull('reminder_2')
                    ->orWhereNotNull('reminder_3');
            })
            ->get();

        $sent = 0;
        $slots = [
            1 => 'reminder_1',
            2 => 'reminder_2',
            3 => 'reminder_3',
        ];

        foreach ($tasks as $task) {
            if (strtolower((string) $task->status) === 'completed') {
                continue;
            }

            foreach ($slots as $slot => $column) {
                $rawValue = $task->{$column};
                if (empty($rawValue)) {
                    continue;
                }

                try {
                    $remindAt = Carbon::parse($rawValue);
                } catch (\Throwable) {
                    continue;
                }

                if (!$remindAt->between($windowStart, $windowEnd)) {
                    continue;
                }

                $alreadySent = TodoReminderLog::query()
                    ->where('todo_task_id', $task->id)
                    ->where('reminder_slot', $slot)
                    ->where('remind_at', $remindAt)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                TodoReminderLog::create([
                    'todo_task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'reminder_slot' => $slot,
                    'remind_at' => $remindAt,
                    'sent_at' => $now,
                ]);

                NotificationLog::create([
                    'user_id' => $task->user_id,
                    'channel' => 'todo',
                    'status' => 'sent',
                    'to' => null,
                    'context' => 'todo_reminder',
                    'context_json' => json_encode([
                        'task_id' => $task->id,
                        'task_no' => $task->task_no,
                        'employee_name' => $task->employee_name,
                        'status' => $task->status,
                        'due_at' => $task->due_at ? Carbon::parse($task->due_at)->toDateTimeString() : null,
                        'reminder_slot' => $slot,
                        'reminder_at' => $remindAt->toDateTimeString(),
                    ]),
                    'error' => null,
                ]);

                $sent++;
            }
        }

        $this->info("To-Do reminders sent: {$sent}");

        return Command::SUCCESS;
    }
}
