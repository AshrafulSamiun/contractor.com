<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailMessage;
use App\Models\EmailSetting;
use Illuminate\Http\Request;

class EmailStatsController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->user()->id;

        $stats = [
            'inbox' => EmailMessage::where('user_id', $userId)->where('folder', 'inbox')->count(),
            'unread' => EmailMessage::where('user_id', $userId)->where('folder', 'inbox')->whereNull('read_at')->count(),
            'sent' => EmailMessage::where('user_id', $userId)->where('folder', 'sent')->count(),
            'drafts' => EmailMessage::where('user_id', $userId)->where('folder', 'drafts')->count(),
            'trash' => EmailMessage::where('user_id', $userId)->where('folder', 'trash')->count(),
            'spam' => EmailMessage::where('user_id', $userId)->where('spam_flag', true)->count(),
        ];

        $settings = EmailSetting::where('user_id', $userId)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'last_sync_at' => $settings?->imap_last_sync_at,
                'last_error' => $settings?->imap_last_error,
            ],
        ]);
    }
}
