<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
protected function schedule(Schedule $schedule): void
{
        $schedule->command('calendar:send-reminders')->everyMinute();
        $schedule->command('todo:send-reminders')->everyMinute();
        $schedule->command('announcements:dispatch')->everyMinute();
        $schedule->command('email:sync-inbox-queue')->everyFiveMinutes();
        $schedule->command('email:cleanup')->daily();
        $schedule->command('workforce:send-approval-reminders')->hourly();
        $schedule->command('pickup:send-rule-reminders')->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
