<?php

namespace App\Console\Commands;

use App\Jobs\SyncEmailInboxJob as SyncJob;
use App\Models\EmailSetting;
use Illuminate\Console\Command;

class SyncEmailInboxJob extends Command
{
    protected $signature = 'email:sync-inbox-queue {--user_id=}';
    protected $description = 'Queue IMAP sync jobs.';

    public function handle(): int
    {
        $userId = $this->option('user_id');
        $query = EmailSetting::query()->where('imap_enabled', true);

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        $query->pluck('user_id')->each(function ($id) {
            SyncJob::dispatch((int) $id);
        });

        $this->info('Sync jobs dispatched.');
        return Command::SUCCESS;
    }
}
