<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceTimesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'timesheet_no',
        'employee_name',
        'employee_code',
        'role_title',
        'week_start',
        'week_end',
        'regular_hours',
        'overtime_hours',
        'total_hours',
        'entries_json',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'week_start' => 'date',
        'week_end' => 'date',
        'entries_json' => 'array',
        'approved_at' => 'datetime',
    ];
}
