<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceDailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_no',
        'report_location',
        'report_date',
        'shift',
        'shift_name',
        'shift_time',
        'summary',
        'report_notes',
        'employee_name',
        'expire_date',
        'licence_no',
        'is_valid',
        'metrics_json',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'shift_time' => 'string',
        'expire_date' => 'date',
        'metrics_json' => 'array',
        'is_valid' => 'boolean',
    ];
}
