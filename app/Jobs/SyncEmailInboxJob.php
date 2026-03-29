<?php

namespace App\Jobs;

use App\Services\EmailSettingsService;
use App\Services\ImapSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEmailInboxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $userId)
    {
    }

    public function handle(EmailSettingsService $settingsService, ImapSyncService $imapSync): void
    {
        $settings = $settingsService->resolveForUser($this->userId);
        $imapSync->syncInbox($this->userId, $settings);
    }
}
