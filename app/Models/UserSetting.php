<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timezone',
        'date_format',
        'time_format',
        'week_start',
        'theme_mode',
        'theme_accent',
        'language_code',
        'holding_default_days',
        'holding_max_days',
        'holding_reminder_days',
        'holding_auto_expire',
        'notify_parcel_arrival',
        'notify_pickup_request',
        'notify_parcel_return',
        'notify_channel_email',
        'notify_channel_sms',
        'notify_channel_in_app',
        'use_global_notifications',
        'notify_delivery_completed',
        'notify_rejected_parcel',
        'notify_lost_damaged',
        'notify_expiry_reminder',
        'notify_channel_whatsapp',
        'notify_channel_push',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'quiet_hours_days',
        'quiet_hours_schedule',
        'template_email_subject',
        'template_email_body',
        'template_sms_body',
        'template_in_app_body',
        'template_whatsapp_body',
        'template_push_title',
        'template_push_body',
    ];
}
