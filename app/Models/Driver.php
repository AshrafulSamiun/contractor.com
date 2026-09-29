<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    public const STATUS = [
        1 => 'Active',
        2 => 'Inactive',
        3 => 'Suspended',
    ];

    public const LICENSE_CLASSES = [
        'Class A',
        'Class B',
        'Class C',
        'Class D',
    ];

    public const EMPLOYMENT_TYPES = [
        'Full Time',
        'Part Time',
        'Contract',
        'Temporary',
    ];

    protected $fillable = [
        'project_id',
        'driver_code',
        'driver_name',
        'contact_number',
        'email',
        'date_of_birth',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'license_number',
        'license_expiry_date',
        'license_class',
        'assigned_vehicle_ids',
        'hire_date',
        'employment_type',
        'hourly_rate',
        'profile_data',
        'status',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'date_of_birth' => 'date',
        'license_expiry_date' => 'date',
        'assigned_vehicle_ids' => 'array',
        'hire_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'profile_data' => 'array',
        'status' => 'integer',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? self::STATUS[1];
    }
}
