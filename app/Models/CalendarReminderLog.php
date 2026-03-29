<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_event_id',
        'user_id',
        'minutes_before',
        'remind_at',
        'sent_at',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
