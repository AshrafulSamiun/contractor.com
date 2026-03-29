<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (self $event): void {
            if ($event->event_no) {
                return;
            }

            $year = $event->created_at?->format('Y') ?? now()->format('Y');
            $event->forceFill([
                'event_no' => sprintf('EVT-%s-%04d', $year, $event->id),
            ])->saveQuietly();
        });
    }

    protected $fillable = [
        'user_id',
        'event_no',
        'title',
        'description',
        'start_at',
        'end_at',
        'all_day',
        'timezone',
        'location',
        'location_map',
        'color',
        'status',
        'visibility',
        'priority',
        'event_type',
        'required_action',
        'recurrence_freq',
        'recurrence_interval',
        'recurrence_days_json',
        'recurrence_rules_json',
        'recurrence_mode',
        'recurrence_end_after',
        'recurrence_until',
        'reminders_json',
        'attendees_json',
        'exceptions_json',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
        'recurrence_until' => 'date',
        'recurrence_days_json' => 'array',
        'recurrence_rules_json' => 'array',
        'recurrence_end_after' => 'integer',
        'reminders_json' => 'array',
        'attendees_json' => 'array',
        'exceptions_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
