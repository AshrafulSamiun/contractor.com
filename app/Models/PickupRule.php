<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupRule extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'allowed_roles',
        'name',
        'verification_method',
        'window_hours',
        'sla_hours',
        'reminder_enabled',
        'reminder_frequency_hours',
        'last_reminder_at',
        'sla_breach_notified_at',
        'reminder_channels',
        'notify_recipient',
        'notify_staff',
        'active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'reminder_channels' => 'array',
        'allowed_roles' => 'array',
        'reminder_enabled' => 'boolean',
        'notify_recipient' => 'boolean',
        'notify_staff' => 'boolean',
        'active' => 'boolean',
        'last_reminder_at' => 'datetime',
        'sla_breach_notified_at' => 'datetime',
    ];
}
