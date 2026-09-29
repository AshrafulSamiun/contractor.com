<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsurancePolicy extends Model
{
    use HasFactory;

    protected $table = 'insurance_policies';

    public const STATUS = [
        1 => 'Active',
        2 => 'Expired',
        3 => 'Pending',
        4 => 'Cancelled',
    ];

    public const COVERAGE_TYPES = [
        'Comprehensive',
        'Third-Party',
        'Collision',
        'Liability',
    ];

    public const CURRENCIES = [
        'USD',
        'BDT',
        'EUR',
        'GBP',
    ];

    public const PAYMENT_FREQUENCIES = [
        'Monthly',
        'Quarterly',
        'Semi-Annual',
        'Annual',
    ];

    protected $fillable = [
        'project_id',
        'insurance_code',
        'vehicle_id',
        'insurance_company',
        'company_phone',
        'company_email',
        'company_address',
        'policy_number',
        'coverage_type',
        'insurance_type',
        'agent_name',
        'agent_phone',
        'agent_email',
        'start_date',
        'expiry_date',
        'coverage_amount',
        'insured_value',
        'deductible',
        'currency',
        'premium_amount',
        'sales_tax',
        'total_paid',
        'payment_frequency',
        'payment_method',
        'charging_date',
        'policy_data',
        'reminders',
        'claim_history',
        'payment_history',
        'document_name',
        'document_path',
        'calendar_reminder',
        'status',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'vehicle_id' => 'integer',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'coverage_amount' => 'decimal:2',
        'insured_value' => 'decimal:2',
        'deductible' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'sales_tax' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'charging_date' => 'date',
        'policy_data' => 'array',
        'reminders' => 'array',
        'claim_history' => 'array',
        'payment_history' => 'array',
        'calendar_reminder' => 'boolean',
        'status' => 'integer',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? self::STATUS[1];
    }
}
