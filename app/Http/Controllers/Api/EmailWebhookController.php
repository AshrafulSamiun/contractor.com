<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailAuditLog;
use App\Models\EmailMessage;
use Illuminate\Http\Request;

class EmailWebhookController extends Controller
{
    public function bounce(Request $request)
    {
        $token = config('email.webhooks.token');
        if ($token && $request->header('X-Email-Token') !== $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $messageId = $request->input('message_id');
        $to = $request->input('to');
        $error = $request->input('error');

        $message = null;
        if ($messageId) {
            $message = EmailMessage::where('message_id', $messageId)->first();
        }
        if (!$message && $to) {
            $message = EmailMessage::where('to_email', $to)->orderByDesc('created_at')->first();
        }
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $message->update([
            'send_status' => 'bounced',
            'send_error' => $error ?: 'Bounce',
        ]);

        EmailAuditLog::create([
            'email_message_id' => $message->id,
            'user_id' => $message->user_id,
            'status' => 'bounced',
            'error' => $error,
        ]);

        return response()->json(['success' => true]);
    }
}
