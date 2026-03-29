<?php

namespace App\Console\Commands;

use App\Models\EmailSetting;
use App\Services\EmailSettingsService;
use App\Services\ImapSyncService;
use Illuminate\Console\Command;

class SyncEmailInbox extends Command
{
    protected $signature = 'email:sync-inbox {--user_id=}';
    protected $description = 'Sync IMAP inbox for users with IMAP enabled.';

    public function handle(EmailSettingsService $settingsService, ImapSyncService $imapSync): int
    {
        $userId = $this->option('user_id');

        $query = EmailSetting::query()
            ->where('imap_enabled', true)
            ->with('user');

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        $settingsList = $query->get();
        foreach ($settingsList as $setting) {
            $settings = $settingsService->resolveForUser($setting->user_id);
            $imapSync->syncInbox($setting->user_id, $settings);
        }

        $this->info('Email sync completed.');
        return Command::SUCCESS;
    }
}
