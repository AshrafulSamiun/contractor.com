<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationTicket extends Model
{
    use HasFactory;

    protected $table = 'violation_tickets';

    public const STATUS = [
        1 => 'Pending',
        2 => 'Paid',
        3 => 'Overdue',
        4 => 'Disputed',
    ];

    public const VIOLATION_TYPES = [
        'Speeding',
        'Parking Violation',
        'Illegal Parking',
        'Running Red Light',
        'Failure to Signal',
        'Illegal U-turn',
        'Expired Registration',
    ];

    public const TICKET_SCOPE = [
        'Business',
        'Personal',
    ];

    public const PAYMENT_METHODS = [
        'Cash',
        'Credit Card',
        'Online',
        'Bank Transfer',
    ];

    protected $fillable = [
        'project_id',
        'ticket_code',
        'issue_date',
        'due_date',
        'vehicle_id',
        'driver_id',
        'violation_type',
        'issued_by',
        'fine_amount',
        'points',
        'ticket_scope',
        'status',
        'is_paid',
        'payment_method',
        'paid_date',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'vehicle_id' => 'integer',
        'driver_id' => 'integer',
        'issue_date' => 'date',
        'due_date' => 'date',
        'fine_amount' => 'decimal:2',
        'points' => 'integer',
        'status' => 'integer',
        'is_paid' => 'boolean',
        'paid_date' => 'date',
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
