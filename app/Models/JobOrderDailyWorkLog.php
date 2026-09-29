<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrderDailyWorkLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_order_id',
        'log_no',
        'period_start',
        'period_end',
        'project_manager',
        'total_wages',
        'total_materials',
        'total_overhead',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'job_order_id' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_wages' => 'decimal:2',
        'total_materials' => 'decimal:2',
        'total_overhead' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JobOrderDailyWorkLogEntry::class, 'daily_work_log_id');
    }
}
