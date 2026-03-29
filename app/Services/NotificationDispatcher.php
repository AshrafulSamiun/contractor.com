<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\UserSetting;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class NotificationDispatcher
{
    protected function loadDefaults(): array
    {
        $row = SystemSetting::firstOrCreate(['key' => 'notification_defaults'], [
            'value' => null,
        ]);

        if ($row->value) {
            $decoded = json_decode($row->value, true);
            if (is_array($decoded)) {
                return array_merge($this->defaultPayload(), $decoded);
            }
        }

        $defaults = $this->defaultPayload();
        $row->value = json_encode($defaults);
        $row->save();

        return $defaults;
    }

    protected function defaultPayload(): array
    {
        return [
            'notify_parcel_arrival' => true,
            'notify_pickup_request' => true,
            'notify_parcel_return' => true,
            'notify_channel_email' => true,
            'notify_channel_sms' => false,
            'notify_channel_in_app' => true,
            'use_global_notifications' => true,
            'notify_delivery_completed' => true,
            'notify_rejected_parcel' => true,
            'notify_lost_damaged' => true,
            'notify_expiry_reminder' => true,
            'notify_channel_whatsapp' => false,
            'notify_channel_push' => true,
            'quiet_hours_enabled' => false,
            'quiet_hours_start' => null,
            'quiet_hours_end' => null,
            'quiet_hours_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'quiet_hours_schedule' => [],
            'template_email_subject' => 'Parcel update',
            'template_email_body' => 'Your parcel status has been updated. Please check your dashboard for details.',
            'template_sms_body' => 'Parcel update: your item status changed. Please check the app.',
            'template_in_app_body' => 'Parcel update: check your dashboard for details.',
            'template_whatsapp_body' => 'Parcel update: your item status changed. Please check the app.',
            'template_push_title' => 'Parcel Update',
            'template_push_body' => 'Your parcel status has been updated.',
        ];
    }

    protected function extractUserSettings(UserSetting $settings): array
    {
        return [
            'notify_parcel_arrival' => (bool) ($settings->notify_parcel_arrival ?? true),
            'notify_pickup_request' => (bool) ($settings->notify_pickup_request ?? true),
            'notify_parcel_return' => (bool) ($settings->notify_parcel_return ?? true),
            'notify_channel_email' => (bool) ($settings->notify_channel_email ?? true),
            'notify_channel_sms' => (bool) ($settings->notify_channel_sms ?? false),
            'notify_channel_in_app' => (bool) ($settings->notify_channel_in_app ?? true),
            'use_global_notifications' => (bool) ($settings->use_global_notifications ?? true),
            'notify_delivery_completed' => (bool) ($settings->notify_delivery_completed ?? true),
            'notify_rejected_parcel' => (bool) ($settings->notify_rejected_parcel ?? true),
            'notify_lost_damaged' => (bool) ($settings->notify_lost_damaged ?? true),
            'notify_expiry_reminder' => (bool) ($settings->notify_expiry_reminder ?? true),
            'notify_channel_whatsapp' => (bool) ($settings->notify_channel_whatsapp ?? false),
            'notify_channel_push' => (bool) ($settings->notify_channel_push ?? true),
            'quiet_hours_enabled' => (bool) ($settings->quiet_hours_enabled ?? false),
            'quiet_hours_start' => $settings->quiet_hours_start,
            'quiet_hours_end' => $settings->quiet_hours_end,
            'quiet_hours_days' => $settings->quiet_hours_days
                ? explode(',', $settings->quiet_hours_days)
                : [],
            'quiet_hours_schedule' => $settings->quiet_hours_schedule
                ? json_decode($settings->quiet_hours_schedule, true)
                : [],
            'template_email_subject' => $settings->template_email_subject,
            'template_email_body' => $settings->template_email_body,
            'template_sms_body' => $settings->template_sms_body,
            'template_in_app_body' => $settings->template_in_app_body,
            'template_whatsapp_body' => $settings->template_whatsapp_body,
            'template_push_title' => $settings->template_push_title,
            'template_push_body' => $settings->template_push_body,
            'timezone' => $settings->timezone,
            'holding_default_days' => $settings->holding_default_days,
        ];
    }
    public function sendParcelEvent($user, $parcel, string $event): void
    {
        $settings = $this->resolveSettings($user->id);

        $eventToggleMap = [
            'parcel_arrival' => 'notify_parcel_arrival',
            'pickup_request' => 'notify_pickup_request',
            'parcel_return' => 'notify_parcel_return',
            'delivery_completed' => 'notify_delivery_completed',
            'rejected_parcel' => 'notify_rejected_parcel',
            'lost_damaged' => 'notify_lost_damaged',
            'expiry_reminder' => 'notify_expiry_reminder',
        ];

        $toggleKey = $eventToggleMap[$event] ?? null;
        if (!$toggleKey || !$settings->{$toggleKey}) {
            $this->log($user->id, 'system', 'skipped_disabled', $user->email, [
                'event' => $event,
                'reason' => 'event_disabled',
            ]);
            return;
        }

        if ($this->isQuietHours($settings)) {
            foreach (['email', 'sms', 'whatsapp', 'push', 'in_app'] as $channel) {
                $this->log($user->id, $channel, 'quiet_hours', $user->email, [
                    'event' => $event,
                    'reason' => 'quiet_hours',
                ]);
            }
            return;
        }

        $context = $this->buildContext($settings, $user, $parcel, $event);

        $this->sendEmail($user, $settings, $context);
        $this->sendSms($user, $settings, $context);
        $this->sendWhatsApp($user, $settings, $context);
        $this->sendPush($user, $settings, $context);
        $this->sendInApp($user, $settings, $context);
    }

    protected function resolveSettings(int $userId): array
    {
        $defaults = $this->loadDefaults();

        $userSettings = UserSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'timezone' => 'UTC',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'week_start' => 'Monday',
                'theme_mode' => 'system',
                'theme_accent' => 'blue',
                'holding_default_days' => 7,
                'holding_max_days' => 30,
                'holding_reminder_days' => 2,
                'holding_auto_expire' => true,
                'notify_parcel_arrival' => true,
                'notify_pickup_request' => true,
                'notify_parcel_return' => true,
                'notify_channel_email' => true,
                'notify_channel_sms' => false,
                'notify_channel_in_app' => true,
                'use_global_notifications' => true,
                'notify_delivery_completed' => true,
                'notify_rejected_parcel' => true,
                'notify_lost_damaged' => true,
                'notify_expiry_reminder' => true,
                'notify_channel_whatsapp' => false,
                'notify_channel_push' => true,
                'quiet_hours_enabled' => false,
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'quiet_hours_days' => 'Mon,Tue,Wed,Thu,Fri',
                'quiet_hours_schedule' => null,
                'template_email_subject' => 'Parcel update',
                'template_email_body' => 'Your parcel status has been updated. Please check your dashboard for details.',
                'template_sms_body' => 'Parcel update: your item status changed. Please check the app.',
                'template_in_app_body' => 'Parcel update: check your dashboard for details.',
                'template_whatsapp_body' => 'Parcel update: your item status changed. Please check the app.',
                'template_push_title' => 'Parcel Update',
                'template_push_body' => 'Your parcel status has been updated.',
            ]
        );

        $useGlobal = (bool) ($userSettings->use_global_notifications ?? true);
        $userData = $this->extractUserSettings($userSettings);
        $merged = array_merge($defaults, $userData);

        if ($useGlobal) {
            $merged = array_merge($merged, $defaults);
            $merged['timezone'] = $userData['timezone'] ?? 'UTC';
            $merged['holding_default_days'] = $userData['holding_default_days'] ?? 7;
        }

        return $merged;
    }

    protected function isQuietHours(array $settings): bool
    {
        if (!($settings['quiet_hours_enabled'] ?? false)) {
            return false;
        }

        $timezone = $settings['timezone'] ?? 'UTC';
        $now = Carbon::now($timezone);
        $day = $now->format('D');

        $schedule = $settings['quiet_hours_schedule'] ?? [];

        if (!$schedule) {
            return false;
        }

        foreach ($schedule as $slot) {
            if (($slot['day'] ?? null) !== $day) {
                continue;
            }
            if (!($slot['enabled'] ?? false)) {
                return false;
            }
            $start = $slot['start'] ?? null;
            $end = $slot['end'] ?? null;
            if (!$start || !$end) {
                return false;
            }
            $startTime = Carbon::createFromFormat('H:i', $start, $timezone)->setDate(
                $now->year,
                $now->month,
                $now->day
            );
            $endTime = Carbon::createFromFormat('H:i', $end, $timezone)->setDate(
                $now->year,
                $now->month,
                $now->day
            );
            if ($startTime->eq($endTime)) {
                return false;
            }
            if ($startTime->lt($endTime)) {
                return $now->between($startTime, $endTime);
            }
            return $now->gte($startTime) || $now->lte($endTime);
        }

        return false;
    }

    protected function buildContext(array $settings, $user, $parcel, string $event): array
    {
        $pickupBy = $parcel->received_at
            ? Carbon::parse($parcel->received_at)->addDays((int) ($settings['holding_default_days'] ?? 7))->toDateString()
            : null;

        return [
            'recipient_name' => $parcel->recipient_name ?? $user->name,
            'parcel_id' => $parcel->tracking_code ?? (string) $parcel->id,
            'tracking_code' => $parcel->tracking_code ?? '',
            'status' => $event,
            'pickup_by' => $pickupBy ?? '',
            'carrier_name' => $parcel->notes ?? '',
            'property_name' => $parcel->facility_name ?? '',
            'facility_name' => $parcel->facility_name ?? '',
            'location' => $parcel->location ?? '',
            'received_at' => optional($parcel->received_at)->toDateTimeString() ?? '',
            'delivered_at' => optional($parcel->delivered_at)->toDateTimeString() ?? '',
            'picked_up_at' => optional($parcel->picked_up_at)->toDateTimeString() ?? '',
            'user_name' => $user->name ?? '',
            'company_name' => $user->company_name ?? '',
            'phone' => $user->phone ?? '',
        ];
    }

    public function sendWorkforceEvent($user, array $context, string $event, ?array $channels = null): void
    {
        $settings = $this->resolveSettings($user->id);

        if ($this->isQuietHours($settings)) {
            foreach (['email', 'in_app'] as $channel) {
                $this->log($user->id, $channel, 'quiet_hours', $user->email, [
                    ...$context,
                    'event' => $event,
                    'reason' => 'quiet_hours',
                ]);
            }
            return;
        }

        $subject = $context['subject'] ?? 'Workforce update';
        $body = $context['message'] ?? 'There is an update in your workforce records.';

        $allowEmail = $settings['notify_channel_email'] ?? false;
        $allowInApp = $settings['notify_channel_in_app'] ?? false;

        if (is_array($channels)) {
            $allowEmail = in_array('email', $channels, true);
            $allowInApp = in_array('in_app', $channels, true);
        }

        if ($allowEmail && $user->email) {
            try {
                Mail::raw($body, function ($message) use ($user, $subject) {
                    $message->to($user->email)->subject($subject);
                });
                $this->log($user->id, 'email', 'sent', $user->email, [
                    ...$context,
                    'event' => $event,
                ]);
            } catch (\Throwable $e) {
                $this->log($user->id, 'email', 'failed', $user->email, [
                    ...$context,
                    'event' => $event,
                ], $e->getMessage());
            }
        } else {
            $this->log($user->id, 'email', 'skipped_disabled', $user->email, [
                ...$context,
                'event' => $event,
            ]);
        }

        if ($allowInApp) {
            $this->log($user->id, 'in_app', 'queued', $user->id, [
                ...$context,
                'event' => $event,
                'message' => $body,
            ]);
        } else {
            $this->log($user->id, 'in_app', 'skipped_disabled', $user->id, [
                ...$context,
                'event' => $event,
            ]);
        }
    }

    protected function sendEmail($user, array $settings, array $context): void
    {
        if (!($settings['notify_channel_email'] ?? false) || !$user->email) {
            $this->log($user->id, 'email', 'skipped_disabled', $user->email, $context);
            return;
        }

        $subject = NotificationTemplateRenderer::render($settings['template_email_subject'] ?? '', $context);
        $body = NotificationTemplateRenderer::render($settings['template_email_body'] ?? '', $context);

        try {
            Mail::raw($body ?: 'Parcel update', function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject ?: 'Parcel update');
            });
            $this->log($user->id, 'email', 'sent', $user->email, $context);
        } catch (\Throwable $e) {
            $this->log($user->id, 'email', 'failed', $user->email, $context, $e->getMessage());
        }
    }

    protected function sendSms($user, array $settings, array $context): void
    {
        if (!($settings['notify_channel_sms'] ?? false)) {
            $this->log($user->id, 'sms', 'skipped_disabled', $user->phone, $context);
            return;
        }

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $from = env('TWILIO_FROM');
        if (!$sid || !$token || !$from) {
            $this->log($user->id, 'sms', 'skipped_missing_config', $user->phone, $context);
            return;
        }
        if (!$user->phone) {
            $this->log($user->id, 'sms', 'skipped_missing_phone', null, $context);
            return;
        }

        $message = NotificationTemplateRenderer::render($settings['template_sms_body'] ?? '', $context);
        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $user->phone,
                    'Body' => $message ?: 'Parcel update',
                ]);
            if (!$response->successful()) {
                $this->log($user->id, 'sms', 'failed', $user->phone, $context, $response->body());
                return;
            }
            $this->log($user->id, 'sms', 'sent', $user->phone, [
                ...$context,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            $this->log($user->id, 'sms', 'failed', $user->phone, $context, $e->getMessage());
        }
    }

    protected function sendWhatsApp($user, array $settings, array $context): void
    {
        if (!($settings['notify_channel_whatsapp'] ?? false)) {
            $this->log($user->id, 'whatsapp', 'skipped_disabled', $user->phone, $context);
            return;
        }

        $message = NotificationTemplateRenderer::render($settings['template_whatsapp_body'] ?? '', $context);
        $this->log($user->id, 'whatsapp', 'queued', $user->phone, [
            ...$context,
            'message' => $message,
        ]);
    }

    protected function sendPush($user, array $settings, array $context): void
    {
        if (!($settings['notify_channel_push'] ?? false)) {
            $this->log($user->id, 'push', 'skipped_disabled', $user->id, $context);
            return;
        }

        $title = NotificationTemplateRenderer::render($settings['template_push_title'] ?? '', $context);
        $body = NotificationTemplateRenderer::render($settings['template_push_body'] ?? '', $context);
        $this->log($user->id, 'push', 'queued', $user->id, [
            ...$context,
            'title' => $title,
            'message' => $body,
        ]);
    }

    protected function sendInApp($user, array $settings, array $context): void
    {
        if (!($settings['notify_channel_in_app'] ?? false)) {
            $this->log($user->id, 'in_app', 'skipped_disabled', $user->id, $context);
            return;
        }

        $body = NotificationTemplateRenderer::render($settings['template_in_app_body'] ?? '', $context);
        $this->log($user->id, 'in_app', 'queued', $user->id, [
            ...$context,
            'message' => $body,
        ]);
    }

    protected function log(int $userId, string $channel, string $status, $to, array $context, ?string $error = null): void
    {
        $contextJson = json_encode($context);
        $contextShort = $contextJson ? substr($contextJson, 0, 50) : null;

        NotificationLog::create([
            'user_id' => $userId,
            'channel' => $channel,
            'status' => $status,
            'to' => $to,
            'context' => $contextShort,
            'context_json' => $contextJson,
            'error' => $error,
        ]);
    }
}
