<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceDailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'report_no',
        'report_location',
        'report_date',
        'shift',
        'shift_name',
        'shift_time',
        'summary',
        'report_notes',
        'employee_name',
        'employee_code',
        'employee_phone',
        'employee_email',
        'expire_date',
        'licence_no',
        'is_valid',
        'worked_on_stat_holiday',
        'metrics_json',
        'details_json',
        'status',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'report_date' => 'date',
        'shift_time' => 'string',
        'expire_date' => 'date',
        'metrics_json' => 'array',
        'details_json' => 'array',
        'is_valid' => 'boolean',
        'worked_on_stat_holiday' => 'boolean',
    ];
}
