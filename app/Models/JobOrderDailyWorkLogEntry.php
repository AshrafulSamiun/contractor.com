<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderDailyWorkLogEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_work_log_id',
        'work_date',
        'day_name',
        'start_time',
        'end_time',
        'activity_description',
        'wages',
        'materials',
        'overhead',
        'daily_total',
        'running_total',
    ];

    protected $casts = [
        'daily_work_log_id' => 'integer',
        'work_date' => 'date',
        'wages' => 'decimal:2',
        'materials' => 'decimal:2',
        'overhead' => 'decimal:2',
        'daily_total' => 'decimal:2',
        'running_total' => 'decimal:2',
    ];

    public function dailyWorkLog(): BelongsTo
    {
        return $this->belongsTo(JobOrderDailyWorkLog::class, 'daily_work_log_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $entry) {
            $entry->daily_total =
                (float) $entry->wages +
                (float) $entry->materials +
                (float) $entry->overhead;
        });
    }
}
