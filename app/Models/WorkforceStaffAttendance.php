<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceStaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'project_id', 'report_no', 'attendance_date', 'prepared_by',
        'staff_name', 'employee_id', 'position_title', 'phone', 'email',
        'location_site', 'customer_job_site', 'department', 'shift_name',
        'check_in_time', 'check_out_time', 'total_minutes', 'status', 'overtime',
        'late_arrival', 'early_departure', 'work_performed', 'notes', 'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'overtime' => 'boolean',
        'late_arrival' => 'boolean',
        'early_departure' => 'boolean',
    ];
}
