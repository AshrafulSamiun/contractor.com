<?php

namespace App\Console\Commands;

use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupEmailData extends Command
{
    protected $signature = 'email:cleanup';
    protected $description = 'Cleanup trashed email messages and orphaned attachments.';

    public function handle(): int
    {
        $days = (int) config('email.retention.trash_days', 30);
        $cutoff = now()->subDays($days);

        $trashed = EmailMessage::where('folder', 'trash')
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        foreach ($trashed as $message) {
            foreach ($message->attachments as $attachment) {
                Storage::delete($attachment->path);
            }
            $message->delete();
        }

        $orphans = EmailAttachment::whereDoesntHave('emailMessage')->get();
        foreach ($orphans as $attachment) {
            Storage::delete($attachment->path);
            $attachment->delete();
        }

        $this->info('Email cleanup completed.');
        return Command::SUCCESS;
    }
}
