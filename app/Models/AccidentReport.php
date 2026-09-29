<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccidentReport extends Model
{
    use HasFactory;

    protected $table = 'accident_reports';

    public const STATUS = [
        1 => 'Open',
        2 => 'Closed',
        3 => 'Pending',
    ];

    public const ACCIDENT_TYPES = [
        'Collision',
        'Breakdown',
        'Minor Accident',
        'Other',
    ];

    public const FAULT_POSITIONS = [
        'Driver',
        'Third Party',
        'Company',
        'Unknown',
    ];

    public const PAYMENT_METHODS = [
        'Cash',
        'Bank Transfer',
        'Card',
        'Insurance',
    ];

    protected $fillable = [
        'project_id',
        'report_code',
        'report_date',
        'created_by_name',
        'incident_report_no',
        'incident_report_name',
        'accident_reference',
        'incident_date',
        'incident_time',
        'location',
        'accident_type',
        'description',
        'vehicle_id',
        'driver_id',
        'at_fault_name',
        'at_fault_position',
        'repair_shop',
        'invoice_no',
        'invoice_date',
        'subtotal_repair_cost',
        'sales_tax_rate',
        'sales_tax_amount',
        'total_cost',
        'payment_method',
        'is_paid',
        'paid_by',
        'amount_paid_by_company',
        'amount_paid_by_insurance',
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
        'report_date' => 'date',
        'incident_date' => 'date',
        'invoice_date' => 'date',
        'subtotal_repair_cost' => 'decimal:2',
        'sales_tax_rate' => 'decimal:2',
        'sales_tax_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_paid' => 'boolean',
        'amount_paid_by_company' => 'decimal:2',
        'amount_paid_by_insurance' => 'decimal:2',
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
