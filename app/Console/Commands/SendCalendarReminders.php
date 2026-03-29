<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use App\Models\CalendarReminderLog;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCalendarReminders extends Command
{
    protected $signature = 'calendar:send-reminders';
    protected $description = 'Send calendar event reminders into notification logs.';

    public function handle(): int
    {
        $now = Carbon::now();
        $windowEnd = $now->copy()->addMinutes(10);

        $events = CalendarEvent::query()
            ->whereNotNull('reminders_json')
            ->where('start_at', '>=', $now->copy()->subMinutes(120))
            ->where('start_at', '<=', $windowEnd->copy()->addDays(1))
            ->get();

        $sent = 0;

        foreach ($events as $event) {
            $reminders = $event->reminders_json ?: [];
            foreach ($reminders as $minutesBefore) {
                $minutesBefore = (int) $minutesBefore;
                if ($minutesBefore <= 0) {
                    continue;
                }
                $remindAt = Carbon::parse($event->start_at)->subMinutes($minutesBefore);
                if (!$remindAt->between($now->copy()->subMinute(), $windowEnd)) {
                    continue;
                }

                $exists = CalendarReminderLog::query()
                    ->where('calendar_event_id', $event->id)
                    ->where('user_id', $event->user_id)
                    ->where('minutes_before', $minutesBefore)
                    ->where('remind_at', $remindAt)
                    ->exists();

                if ($exists) {
                    continue;
                }

                CalendarReminderLog::create([
                    'calendar_event_id' => $event->id,
                    'user_id' => $event->user_id,
                    'minutes_before' => $minutesBefore,
                    'remind_at' => $remindAt,
                    'sent_at' => $now,
                ]);

                NotificationLog::create([
                    'user_id' => $event->user_id,
                    'channel' => 'calendar',
                    'status' => 'sent',
                    'to' => null,
                    'context' => 'calendar_reminder',
                    'context_json' => json_encode([
                        'event_id' => $event->id,
                        'title' => $event->title,
                        'start_at' => $event->start_at,
                        'minutes_before' => $minutesBefore,
                    ]),
                    'error' => null,
                ]);

                $sent++;
            }
        }

        $this->info("Reminders sent: {$sent}");
        return Command::SUCCESS;
    }
}
