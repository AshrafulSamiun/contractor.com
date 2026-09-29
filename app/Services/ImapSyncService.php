<?php

namespace App\Services;

use App\Models\EmailMessage;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ImapSyncService
{
    public function syncInbox(int $userId, array $settings, int $limit = 50): array
    {
        if (!function_exists('imap_open')) {
            EmailSetting::where('user_id', $userId)->update([
                'imap_last_sync_at' => now(),
                'imap_last_error' => 'PHP IMAP extension not enabled.',
            ]);
            return ['success' => false, 'message' => 'PHP IMAP extension not enabled.'];
        }

        if (empty($settings['imap_enabled']) || empty($settings['imap_host'])) {
            EmailSetting::where('user_id', $userId)->update([
                'imap_last_sync_at' => now(),
                'imap_last_error' => 'IMAP is not configured.',
            ]);
            return ['success' => false, 'message' => 'IMAP is not configured.'];
        }

        $host = $settings['imap_host'];
        $port = $settings['imap_port'] ?: 993;
        $encryption = $settings['imap_encryption'] ?: 'ssl';
        $username = $settings['imap_username'] ?? '';
        $password = $settings['imap_password'] ?? '';
        if ($password) {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable $e) {
                // use raw value if decryption fails
            }
        }
        $folder = $settings['imap_folder'] ?: 'INBOX';

        $mailbox = sprintf('{%s:%d/imap/%s}%s', $host, $port, $encryption, $folder);
        $inbox = @imap_open($mailbox, $username, $password);
        if (!$inbox) {
            $error = imap_last_error() ?: 'IMAP connection failed.';
            EmailSetting::where('user_id', $userId)->update([
                'imap_last_sync_at' => now(),
                'imap_last_error' => $error,
            ]);
            return ['success' => false, 'message' => $error];
        }

        $lastUid = (int) ($settings['imap_last_uid'] ?? 0);
        $uids = imap_search($inbox, 'ALL', SE_UID);
        if (!$uids) {
            imap_close($inbox);
            EmailSetting::where('user_id', $userId)->update([
                'imap_last_sync_at' => now(),
                'imap_last_error' => null,
            ]);
            return ['success' => true, 'imported' => 0];
        }

        $uids = array_filter($uids, fn($uid) => $uid > $lastUid);
        $uids = array_slice($uids, -$limit);
        $imported = 0;

        foreach ($uids as $uid) {
            $overview = imap_fetch_overview($inbox, $uid, FT_UID);
            $ov = $overview[0] ?? null;
            if (!$ov) {
                continue;
            }

            $messageId = isset($ov->message_id) ? trim($ov->message_id) : null;
            if ($messageId && EmailMessage::where('user_id', $userId)->where('message_id', $messageId)->exists()) {
                $lastUid = max($lastUid, $uid);
                continue;
            }

            $headers = imap_fetchheader($inbox, $uid, FT_UID);
            $structure = imap_fetchstructure($inbox, $uid, FT_UID);
            $bodyHtml = $this->getBody($inbox, $uid, $structure, true);
            $bodyText = $this->getBody($inbox, $uid, $structure, false);

            $fromEmail = $this->extractFromEmail($headers) ?: ($ov->from ?? null);
            $fromName = $this->extractFromName($headers);
            $replyTo = $this->extractHeader($headers, 'Reply-To');
            $cc = $this->extractHeader($headers, 'Cc');
            $bcc = $this->extractHeader($headers, 'Bcc');
            $subject = isset($ov->subject) ? imap_utf8($ov->subject) : null;
            $date = isset($ov->date) ? $ov->date : null;
            $inReplyTo = $this->extractHeader($headers, 'In-Reply-To');
            $references = $this->extractHeader($headers, 'References');

            $threadId = $this->resolveThreadId($userId, $inReplyTo, $references, $subject);
            $messageId = $messageId ?: ('<' . Str::uuid()->toString() . '@contractor.com>');

            $spam = $this->scoreSpam($subject, $bodyText, $bodyHtml);

            $message = EmailMessage::create([
                'user_id' => $userId,
                'folder' => 'inbox',
                'direction' => 'inbound',
                'status' => 'unread',
                'thread_id' => $threadId,
                'message_id' => $messageId,
                'in_reply_to' => $inReplyTo,
                'references' => $references,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_email' => $settings['imap_username'] ?? null,
                'reply_to' => $replyTo,
                'cc' => $cc ? [$cc] : [],
                'bcc' => $bcc ? [$bcc] : [],
                'subject' => $subject,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
                'spam_score' => $spam['score'],
                'spam_flag' => $spam['flag'],
                'sent_at' => $date ? date('Y-m-d H:i:s', strtotime($date)) : null,
            ]);

            $this->storeAttachments($inbox, $uid, $structure, $message, $userId);

            $imported++;
            $lastUid = max($lastUid, $uid);
        }

        imap_close($inbox);

        EmailSetting::where('user_id', $userId)->update([
            'imap_last_uid' => $lastUid,
            'imap_last_sync_at' => now(),
            'imap_last_error' => null,
        ]);

        return ['success' => true, 'imported' => $imported];
    }

    protected function resolveThreadId(int $userId, ?string $inReplyTo, ?string $references, ?string $subject): string
    {
        $referenceId = $inReplyTo ?: ($references ? trim(explode(' ', trim($references))[0]) : null);
        if ($referenceId) {
            $message = EmailMessage::where('user_id', $userId)->where('message_id', $referenceId)->first();
            if ($message && $message->thread_id) {
                return $message->thread_id;
            }
        }

        if ($subject) {
            $normalized = preg_replace('/^re:\s*/i', '', $subject);
            $message = EmailMessage::where('user_id', $userId)
                ->where('subject', 'like', $normalized . '%')
                ->orderByDesc('created_at')
                ->first();
            if ($message && $message->thread_id) {
                return $message->thread_id;
            }
        }

        return Str::uuid()->toString();
    }

    protected function extractHeader(string $headers, string $key): ?string
    {
        if (preg_match('/^' . preg_quote($key, '/') . ':\s*(.+)$/im', $headers, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function extractFromEmail(string $headers): ?string
    {
        if (preg_match('/^From:\s*(.+)$/im', $headers, $matches)) {
            if (preg_match('/<([^>]+)>/', $matches[1], $emailMatch)) {
                return trim($emailMatch[1]);
            }
            return trim($matches[1]);
        }
        return null;
    }

    protected function extractFromName(string $headers): ?string
    {
        if (preg_match('/^From:\s*(.+)$/im', $headers, $matches)) {
            $raw = trim($matches[1]);
            $raw = preg_replace('/<[^>]+>/', '', $raw);
            $raw = trim($raw, "\" \t\n\r\0\x0B");
            return $raw ?: null;
        }
        return null;
    }

    protected function getBody($inbox, int $uid, $structure, bool $html): ?string
    {
        if (!$structure) {
            return null;
        }
        if (!isset($structure->parts)) {
            $body = imap_body($inbox, $uid, FT_UID);
            return $this->decodeBody($body, $structure->encoding);
        }

        foreach ($structure->parts as $index => $part) {
            $isHtml = isset($part->subtype) && strtoupper($part->subtype) === 'HTML';
            $isText = isset($part->subtype) && strtoupper($part->subtype) === 'PLAIN';
            if (($html && $isHtml) || (!$html && $isText)) {
                $body = imap_fetchbody($inbox, $uid, $index + 1, FT_UID);
                return $this->decodeBody($body, $part->encoding);
            }
        }

        return null;
    }

    protected function decodeBody(string $body, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($body),
            4 => quoted_printable_decode($body),
            default => $body,
        };
    }

    protected function storeAttachments($inbox, int $uid, $structure, EmailMessage $message, int $userId): void
    {
        if (!isset($structure->parts)) {
            return;
        }

        $allowedMimes = config('email.attachments.allowed_mimes', []);
        $maxFileMb = (int) config('email.attachments.max_file_mb', 5);
        $maxTotalMb = (int) config('email.attachments.max_total_mb', 25);
        $maxUserTotalMb = (int) config('email.attachments.max_user_total_mb', 200);
        $maxTotalBytes = $maxTotalMb * 1024 * 1024;
        $maxUserTotalBytes = $maxUserTotalMb * 1024 * 1024;
        $currentTotal = 0;
        $userTotal = (int) \App\Models\EmailAttachment::whereHas('emailMessage', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum('size');

        foreach ($structure->parts as $index => $part) {
            $isAttachment = isset($part->ifdparameters) && $part->ifdparameters;
            if (!$isAttachment) {
                continue;
            }
            $filename = null;
            foreach ($part->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    $filename = $param->value;
                    break;
                }
            }
            if (!$filename) {
                continue;
            }

            $body = imap_fetchbody($inbox, $uid, $index + 1, FT_UID);
            $decoded = $this->decodeBody($body, $part->encoding);
            $size = strlen($decoded);
            if ($size > ($maxFileMb * 1024 * 1024)) {
                continue;
            }
            if ($currentTotal + $size > $maxTotalBytes) {
                break;
            }
            if ($userTotal + $size > $maxUserTotalBytes) {
                break;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $blocked = config('email.attachments.blocked_extensions', []);
            if ($ext && in_array($ext, $blocked, true)) {
                continue;
            }

            $mime = $this->partMime($part);
            if ($allowedMimes && $mime && !in_array($mime, $allowedMimes, true)) {
                continue;
            }

            $path = 'email_attachments/' . $userId . '/' . $message->id . '/' . $filename;
            \Illuminate\Support\Facades\Storage::put($path, $decoded);
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);
            if (!$this->scanFile($fullPath)) {
                \Illuminate\Support\Facades\Storage::delete($path);
                continue;
            }
            \App\Models\EmailAttachment::create([
                'email_message_id' => $message->id,
                'original_name' => $filename,
                'path' => $path,
                'mime' => $mime,
                'size' => $size,
            ]);
            $currentTotal += $size;
            $userTotal += $size;
        }
    }

    protected function partMime($part): ?string
    {
        $primary = $part->type ?? null;
        $subtype = $part->subtype ?? null;
        if ($primary === null || !$subtype) {
            return null;
        }
        $primaryMap = [
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other',
        ];
        $type = $primaryMap[$primary] ?? 'application';
        return $type . '/' . strtolower($subtype);
    }

    protected function scoreSpam(?string $subject, ?string $bodyText, ?string $bodyHtml): array
    {
        $text = strtolower(trim(($subject ?? '') . ' ' . ($bodyText ?? '') . ' ' . strip_tags($bodyHtml ?? '')));
        $score = 0;
        $keywords = [
            'free',
            'winner',
            'urgent',
            'limited time',
            'click here',
            'verify account',
            'password',
            'bank',
            'invoice',
            'wire transfer',
            'bitcoin',
            'gift card',
        ];
        foreach ($keywords as $word) {
            if (str_contains($text, $word)) {
                $score += 10;
            }
        }
        if (substr_count($text, 'http') > 3) {
            $score += 10;
        }
        $flag = $score >= 30;
        return ['score' => $score, 'flag' => $flag];
    }

    protected function scanFile(string $path): bool
    {
        if (!config('email.attachments.virus_scan_enabled')) {
            return true;
        }
        $cmd = config('email.attachments.virus_scan_command', '');
        if (!$cmd) {
            return false;
        }
        $escaped = escapeshellarg($path);
        $full = $cmd . ' ' . $escaped;
        $result = shell_exec($full);
        if ($result === null) {
            return false;
        }
        return str_contains($result, 'OK');
    }
}
