<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    use HasFactory;

    protected $table = 'daily_logs';

    public const STATUS = [
        1 => 'Active',
        2 => 'Completed',
        3 => 'Cancelled',
    ];

    protected $fillable = [
        'project_id',
        'log_code',
        'log_date',
        'vehicle_id',
        'driver_id',
        'start_time',
        'end_time',
        'start_odometer',
        'end_odometer',
        'miles_driven',
        'fuel_added',
        'purpose_location',
        'status',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'vehicle_id' => 'integer',
        'driver_id' => 'integer',
        'log_date' => 'date',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'miles_driven' => 'integer',
        'fuel_added' => 'decimal:2',
        'status' => 'integer',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? self::STATUS[1];
    }
}
