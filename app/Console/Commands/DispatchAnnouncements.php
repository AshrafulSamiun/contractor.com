<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\NotificationLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchAnnouncements extends Command
{
    protected $signature = 'announcements:dispatch';
    protected $description = 'Notify users about newly published announcements.';

    public function handle(): int
    {
        $now = Carbon::now();
        $announcements = Announcement::query()
            ->where('status', 'published')
            ->whereNull('notified_at')
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->get();

        $count = 0;
        foreach ($announcements as $announcement) {
            $users = User::query();
            if ($announcement->audience === 'admins') {
                $users->where('role', 'admin');
            } elseif ($announcement->audience === 'staff') {
                $users->where('role', '!=', 'admin');
            }
            if ($announcement->target_roles) {
                $users->whereIn('role', $announcement->target_roles);
            }
            $targets = $users->get();

            foreach ($targets as $user) {
                NotificationLog::create([
                    'user_id' => $user->id,
                    'channel' => 'announcement',
                    'status' => 'sent',
                    'to' => null,
                    'context' => 'announcement',
                    'context_json' => json_encode([
                        'announcement_id' => $announcement->id,
                        'title' => $announcement->title,
                        'priority' => $announcement->priority,
                    ]),
                    'error' => null,
                ]);
                $count++;
            }

            $announcement->update(['notified_at' => $now]);
        }

        $this->info("Announcements dispatched: {$count}");
        return Command::SUCCESS;
    }
}
