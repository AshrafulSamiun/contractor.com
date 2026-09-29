<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailMessage;
use App\Models\EmailAttachment;
use App\Models\EmailAuditLog;
use App\Services\EmailSettingsService;
use App\Services\ImapSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $folder = $request->string('folder')->toString() ?: 'inbox';

        $query = EmailMessage::query()
            ->where('user_id', $user->id)
            ->where('folder', $folder)
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                try {
                    $q->whereFullText(['subject', 'body_text'], $term)
                        ->orWhere('to_email', 'like', "%{$term}%")
                        ->orWhere('from_email', 'like', "%{$term}%");
                } catch (\Throwable $e) {
                    $q->where('subject', 'like', "%{$term}%")
                        ->orWhere('body_text', 'like', "%{$term}%")
                        ->orWhere('to_email', 'like', "%{$term}%")
                        ->orWhere('from_email', 'like', "%{$term}%");
                }
            });
        }

        if ($request->boolean('threaded') && $folder === 'inbox') {
            $threadIds = (clone $query)
                ->reorder()
                ->selectRaw('thread_id, max(created_at) as latest')
                ->whereNotNull('thread_id')
                ->groupBy('thread_id')
                ->orderByDesc('latest')
                ->get()
                ->pluck('thread_id')
                ->filter()
                ->values();

            $messages = EmailMessage::query()
                ->where('user_id', $user->id)
                ->where('folder', $folder)
                ->whereIn('thread_id', $threadIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('thread_id');

            $threads = $threadIds->map(function ($threadId) use ($messages) {
                $items = $messages->get($threadId, collect());
                $latest = $items->first();
                $unreadCount = $items->whereNull('read_at')->count();
                $spamFlag = $items->where('spam_flag', true)->count() > 0;
                $preview = $latest?->body_text;
                if (!$preview && $latest?->body_html) {
                    $preview = strip_tags($latest->body_html);
                }
                if ($preview) {
                    $preview = substr(trim($preview), 0, 90);
                }
                return [
                    'thread_id' => $threadId,
                    'subject' => $latest?->subject,
                    'from_email' => $latest?->from_email,
                    'to_email' => $latest?->to_email,
                    'status' => $latest?->status,
                    'latest_at' => $latest?->created_at,
                    'count' => $items->count(),
                    'unread_count' => $unreadCount,
                    'preview' => $preview,
                    'spam_flag' => $spamFlag,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $threads,
            ]);
        }

        $perPage = (int) $request->get('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);

        if (!$message->read_at && $message->folder === 'inbox') {
            $message->update(['read_at' => now(), 'status' => 'read']);
        }

        $message->load('attachments', 'auditLogs');

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_email' => ['nullable', 'email'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['email'],
            'reply_to' => ['nullable', 'email'],
            'status' => ['nullable', 'in:draft,sent'],
            'thread_id' => ['nullable', 'string', 'max:64'],
            'in_reply_to' => ['nullable', 'string', 'max:255'],
        ]);

        $threadId = $validated['thread_id'] ?? Str::uuid()->toString();

        $message = EmailMessage::create([
            'user_id' => $request->user()->id,
            'folder' => ($validated['status'] ?? 'draft') === 'sent' ? 'sent' : 'drafts',
            'direction' => 'outbound',
            'status' => $validated['status'] ?? 'draft',
            'thread_id' => $threadId,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ], 201);
    }

    public function update(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);

        if ($message->folder !== 'drafts') {
            return response()->json([
                'message' => 'Only draft messages can be updated.',
            ], 422);
        }

        $validated = $request->validate([
            'to_email' => ['nullable', 'email'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['email'],
            'reply_to' => ['nullable', 'email'],
            'thread_id' => ['nullable', 'string', 'max:64'],
            'in_reply_to' => ['nullable', 'string', 'max:255'],
        ]);

        $message->update($validated);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function send(Request $request, EmailMessage $message, EmailSettingsService $settingsService)
    {
        $this->authorizeMessage($request, $message);

        $validated = $request->validate([
            'to_email' => ['nullable', 'email'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['email'],
            'reply_to' => ['nullable', 'email'],
            'thread_id' => ['nullable', 'string', 'max:64'],
            'in_reply_to' => ['nullable', 'string', 'max:255'],
        ]);

        $message->fill($validated);
        if (!$message->thread_id) {
            if ($message->in_reply_to) {
                $parent = EmailMessage::where('user_id', $request->user()->id)
                    ->where('message_id', $message->in_reply_to)
                    ->first();
                if ($parent && $parent->thread_id) {
                    $message->thread_id = $parent->thread_id;
                    $message->references = $parent->references ?: $parent->message_id;
                } else {
                    $message->thread_id = Str::uuid()->toString();
                }
            } else {
                $message->thread_id = Str::uuid()->toString();
            }
        }
        if ($message->in_reply_to && !$message->references) {
            $message->references = $message->in_reply_to;
        }

        if (!$message->to_email) {
            return response()->json(['message' => 'Recipient email required.'], 422);
        }
        if (!$message->subject) {
            return response()->json(['message' => 'Subject required.'], 422);
        }

        $settings = $settingsService->resolveForUser($request->user()->id);

        $fromEmail = $settings['from_email'] ?: config('mail.from.address');
        $fromName = $settings['from_name'] ?: config('mail.from.name');
        $replyTo = $message->reply_to ?: $settings['reply_to'];

        $bodyHtml = $message->body_html;
        $bodyText = $message->body_text;
        $cc = $message->cc ?: ($settings['default_cc'] ?? []);
        $bcc = $message->bcc ?: ($settings['default_bcc'] ?? []);

        if ($settings['signature_html']) {
            $bodyHtml = $bodyHtml ? $bodyHtml . '<hr>' . $settings['signature_html'] : $settings['signature_html'];
        }
        if ($settings['signature_text']) {
            $bodyText = $bodyText ? $bodyText . "\n\n" . $settings['signature_text'] : $settings['signature_text'];
        }

        $messageId = $message->message_id ? trim($message->message_id, '<>') : (Str::uuid()->toString() . '@contractor.com');
        $message->message_id = $messageId;

        $attachments = $message->attachments()->get();

        try {
            Mail::send([], [], function ($mail) use ($message, $fromEmail, $fromName, $replyTo, $bodyHtml, $bodyText, $cc, $bcc, $messageId, $attachments) {
                $mail->to($message->to_email)->subject($message->subject);
                if ($fromEmail) {
                    $mail->from($fromEmail, $fromName);
                }
                if ($replyTo) {
                    $mail->replyTo($replyTo);
                }
                if ($cc) {
                    $mail->cc($cc);
                }
                if ($bcc) {
                    $mail->bcc($bcc);
                }
                $mail->getHeaders()->addIdHeader('Message-Id', $messageId);
                if ($message->in_reply_to) {
                    $mail->getHeaders()->addIdHeader('In-Reply-To', trim($message->in_reply_to, '<>'));
                }
                if ($message->references) {
                    $mail->getHeaders()->addIdHeader('References', trim($message->references, '<>'));
                }
                foreach ($attachments as $attachment) {
                    $path = Storage::path($attachment->path);
                    if (file_exists($path)) {
                        $mail->attach($path, [
                            'as' => $attachment->original_name,
                            'mime' => $attachment->mime,
                        ]);
                    }
                }

                if ($bodyHtml) {
                    $mail->html($bodyHtml);
                }
                if ($bodyText) {
                    $mail->text($bodyText);
                }
                if (!$bodyHtml && !$bodyText) {
                    $mail->text('No content.');
                }
            });
        } catch (\Throwable $e) {
            $message->update([
                'send_status' => 'failed',
                'send_error' => $e->getMessage(),
            ]);
            EmailAuditLog::create([
                'email_message_id' => $message->id,
                'user_id' => $request->user()->id,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Email send failed.',
            ], 500);
        }

        $message->update([
            'status' => 'sent',
            'folder' => 'sent',
            'direction' => 'outbound',
            'sent_at' => now(),
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'message_id' => $messageId,
            'send_status' => 'sent',
            'send_error' => null,
        ]);

        EmailAuditLog::create([
            'email_message_id' => $message->id,
            'user_id' => $request->user()->id,
            'status' => 'sent',
            'error' => null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function trash(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);

        $message->update([
            'folder' => 'trash',
            'status' => 'trashed',
            'deleted_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function restore(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);

        $folder = $message->direction === 'outbound' ? 'sent' : 'inbox';

        $message->update([
            'folder' => $folder,
            'status' => $folder === 'inbox' ? 'unread' : 'sent',
            'deleted_at' => null,
        ]);

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function destroy(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);
        foreach ($message->attachments as $attachment) {
            Storage::delete($attachment->path);
        }
        $message->delete();

        return response()->json(['success' => true]);
    }

    public function uploadAttachment(Request $request, EmailMessage $message)
    {
        $this->authorizeMessage($request, $message);

        $maxFileMb = (int) config('email.attachments.max_file_mb', 5);
        $maxTotalMb = (int) config('email.attachments.max_total_mb', 25);
        $allowedMimes = config('email.attachments.allowed_mimes', []);

        $validated = $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:' . ($maxFileMb * 1024)],
        ]);

        $userTotal = (int) EmailAttachment::whereHas('emailMessage', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->sum('size');
        $maxUserTotalMb = (int) config('email.attachments.max_user_total_mb', 200);
        $maxUserTotalBytes = $maxUserTotalMb * 1024 * 1024;

        $currentTotal = (int) $message->attachments()->sum('size');
        $incomingTotal = collect($validated['files'])->sum(fn($file) => $file->getSize());
        $maxTotalBytes = $maxTotalMb * 1024 * 1024;
        if ($currentTotal + $incomingTotal > $maxTotalBytes) {
            return response()->json([
                'message' => 'Total attachments exceed limit.',
                'errors' => [
                    'files' => ['Total attachments exceed limit of ' . $maxTotalMb . 'MB.'],
                ],
            ], 422);
        }
        if ($userTotal + $incomingTotal > $maxUserTotalBytes) {
            return response()->json([
                'message' => 'User attachment quota exceeded.',
                'errors' => [
                    'files' => ['User attachment quota exceeded (' . $maxUserTotalMb . 'MB).'],
                ],
            ], 422);
        }

        $saved = [];
        foreach ($validated['files'] as $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: '');
            $blocked = config('email.attachments.blocked_extensions', []);
            if ($ext && in_array($ext, $blocked, true)) {
                return response()->json([
                    'message' => 'Blocked file type.',
                    'errors' => [
                        'files' => ['Blocked file type: ' . $ext],
                    ],
                ], 422);
            }
            if ($allowedMimes && !in_array($file->getClientMimeType(), $allowedMimes, true)) {
                continue;
            }
            $path = $file->store('email_attachments/' . $request->user()->id . '/' . $message->id);
            if (!$this->scanFile(Storage::path($path))) {
                Storage::delete($path);
                return response()->json([
                    'message' => 'Virus scan failed.',
                    'errors' => [
                        'files' => ['Virus scan failed for uploaded file.'],
                    ],
                ], 422);
            }
            $saved[] = EmailAttachment::create([
                'email_message_id' => $message->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $saved,
        ]);
    }

    public function deleteAttachment(Request $request, EmailMessage $message, EmailAttachment $attachment)
    {
        $this->authorizeMessage($request, $message);

        if ($attachment->email_message_id !== $message->id) {
            abort(404);
        }

        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function downloadAttachment(Request $request, EmailMessage $message, EmailAttachment $attachment)
    {
        $this->authorizeMessage($request, $message);
        if ($attachment->email_message_id !== $message->id) {
            abort(404);
        }
        return Storage::download($attachment->path, $attachment->original_name);
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

    protected function authorizeMessage(Request $request, EmailMessage $message): void
    {
        if ($message->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function thread(Request $request, string $threadId)
    {
        $messages = EmailMessage::query()
            ->where('user_id', $request->user()->id)
            ->where('thread_id', $threadId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function markThread(Request $request, string $threadId)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:read,unread'],
        ]);

        $readAt = $validated['status'] === 'read' ? now() : null;
        EmailMessage::where('user_id', $request->user()->id)
            ->where('thread_id', $threadId)
            ->update([
                'status' => $validated['status'],
                'read_at' => $readAt,
            ]);

        return response()->json(['success' => true]);
    }

    public function sync(Request $request, EmailSettingsService $settingsService, ImapSyncService $imapSync)
    {
        $settings = $settingsService->resolveForUser($request->user()->id);
        $result = $imapSync->syncInbox($request->user()->id, $settings);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? null,
            'data' => $result,
        ]);
    }
}
