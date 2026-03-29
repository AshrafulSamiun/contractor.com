<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceIncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'incident_no',
        'jobsite',
        'shift_name',
        'occurred_at',
        'location',
        'incident_location',
        'severity',
        'description',
        'actions_taken',
        'incident_notes',
        'employee_name',
        'expire_date',
        'license_no',
        'is_valid',
        'incident_type',
        'incident_categories',
        'incident_description',
        'incident_damages',
        'incident_injuries',
        'action_taken',
        'involved_people',
        'witness_people',
        'police_called',
        'police_file_no',
        'police_officer_name',
        'badge_no',
        'fire_dept_called',
        'ambulance_called',
        'emergency_details',
        'status',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'expire_date' => 'date',
        'incident_categories' => 'array',
        'involved_people' => 'array',
        'witness_people' => 'array',
        'police_called' => 'boolean',
        'fire_dept_called' => 'boolean',
        'ambulance_called' => 'boolean',
        'is_valid' => 'boolean',
    ];
}
