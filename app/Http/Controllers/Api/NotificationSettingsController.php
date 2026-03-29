<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationSettingsController extends Controller
{
    public function show(Request $request)
    {
        $defaults = $this->loadDefaults();

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
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

        $useGlobal = (bool) ($settings->use_global_notifications ?? true);
        $data = $useGlobal ? $defaults : array_merge($defaults, $this->extractSettings($settings));

        return response()->json([
            'success' => true,
            'data' => [
                ...$data,
                'use_global_notifications' => $useGlobal,
                'defaults' => $defaults,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'notify_parcel_arrival' => ['required', 'boolean'],
            'notify_pickup_request' => ['required', 'boolean'],
            'notify_parcel_return' => ['required', 'boolean'],
            'notify_channel_email' => ['required', 'boolean'],
            'notify_channel_sms' => ['required', 'boolean'],
            'notify_channel_in_app' => ['required', 'boolean'],
            'use_global_notifications' => ['nullable', 'boolean'],
            'notify_delivery_completed' => ['required', 'boolean'],
            'notify_rejected_parcel' => ['required', 'boolean'],
            'notify_lost_damaged' => ['required', 'boolean'],
            'notify_expiry_reminder' => ['required', 'boolean'],
            'notify_channel_whatsapp' => ['required', 'boolean'],
            'notify_channel_push' => ['required', 'boolean'],
            'quiet_hours_enabled' => ['required', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'quiet_hours_days' => ['nullable', 'array'],
            'quiet_hours_days.*' => ['string', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'quiet_hours_schedule' => ['nullable', 'array'],
            'quiet_hours_schedule.*.day' => ['required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'quiet_hours_schedule.*.enabled' => ['nullable', 'boolean'],
            'quiet_hours_schedule.*.start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_schedule.*.end' => ['nullable', 'date_format:H:i'],
            'template_email_subject' => ['required', 'string', 'max:120'],
            'template_email_body' => ['nullable', 'string'],
            'template_sms_body' => ['nullable', 'string', 'max:160'],
            'template_in_app_body' => ['nullable', 'string', 'max:160'],
            'template_whatsapp_body' => ['nullable', 'string', 'max:300'],
            'template_push_title' => ['nullable', 'string', 'max:80'],
            'template_push_body' => ['nullable', 'string', 'max:160'],
        ]);

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
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

        $quietSchedule = $data['quiet_hours_schedule'] ?? [];
        if ($data['quiet_hours_enabled'] && $quietSchedule) {
            foreach ($quietSchedule as $slot) {
                $enabled = (bool) ($slot['enabled'] ?? false);
                if (!$enabled) {
                    continue;
                }
                $start = $slot['start'] ?? null;
                $end = $slot['end'] ?? null;
                if (!$start || !$end) {
                    return response()->json([
                        'message' => 'Quiet hours schedule requires start and end time for enabled days.',
                        'errors' => [
                            'quiet_hours_schedule' => ['Start and end time are required for enabled days.'],
                        ],
                    ], 422);
                }
                if ($start === $end) {
                    return response()->json([
                        'message' => 'Quiet hours start and end time cannot be the same.',
                        'errors' => [
                            'quiet_hours_schedule' => ['Start and end time cannot be the same for enabled days.'],
                        ],
                    ], 422);
                }
            }
        }

        $quietDays = $data['quiet_hours_days'] ?? [];
        $scope = $request->string('scope')->toString();
        if ($scope === 'global' && $request->user()->role === 'admin') {
            $defaults = $this->loadDefaults();
            $updated = array_merge($defaults, $data);
            $updated['quiet_hours_days'] = $quietDays;
            $updated['quiet_hours_schedule'] = $quietSchedule;
            $this->saveDefaults($updated);
        } else {
            $data['quiet_hours_days'] = $quietDays ? implode(',', $quietDays) : null;
            $data['quiet_hours_schedule'] = $quietSchedule ? json_encode($quietSchedule) : null;
            $settings->update($data);
        }

        return response()->json([
            'success' => true,
            'data' => $this->extractSettings($settings),
        ]);
    }

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

    protected function saveDefaults(array $data): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'notification_defaults'],
            ['value' => json_encode($data)]
        );
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

    protected function extractSettings(UserSetting $settings): array
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
        ];
    }
}
