<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_id',
        'estimation_id',
        'job_order_no',
        'issue_date',
        'status',
        'job_type',
        'job_description',
        'customer_id',
        'job_site_id',
        'site_contact_person',
        'site_contact_number',
        'map_link',
        'schedule_start_date',
        'schedule_end_date',
        'scope_of_work',
        'sub_total',
        'tax',
        'discount',
        'total',
        'note',
        'payment_method',
        'deposit_received',
        'amount_paid',
        'outstanding_balance',
        'payment_status',
        'progress',
        'converted_to_invoice',
        'invoice_reference',
        'job_order_time', 'priority', 'customer_approved', 'customer_type',
        'site_floor_level', 'site_suite_unit', 'site_city', 'site_province', 'site_postal_code', 'site_access_details',
        'request_date', 'request_time', 'requested_by', 'request_method', 'reference_no', 'account_no',
        'alternate_date', 'alternate_start_time', 'alternate_end_time', 'alternate_duration', 'service_notes',
        'access_requirements', 'key_fob_required', 'key_provided_by', 'equipment_required', 'permits_required',
        'permit_details', 'safety_requirements', 'insurance_provided', 'insurance_expiry_date', 'wcb_no', 'customer_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'schedule_start_date' => 'datetime',
        'schedule_end_date' => 'datetime',
        'quotation_id' => 'integer',
        'estimation_id' => 'integer',
        'status' => 'integer',
        'job_type' => 'integer',
        'customer_id' => 'integer',
        'job_site_id' => 'integer',
        'sub_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_method' => 'integer',
        'deposit_received' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'payment_status' => 'integer',
        'progress' => 'integer',
        'converted_to_invoice' => 'boolean',
        'customer_approved' => 'boolean',
        'request_date' => 'date',
        'alternate_date' => 'date',
        'insurance_expiry_date' => 'date',
    ];

    public const STATUS = [
        1 => 'Pending',
        2 => 'Scheduled',
        3 => 'In Progress',
        4 => 'On Hold',
        5 => 'Completed',
        6 => 'Cancelled',
    ];

    public const JOB_TYPE = [
        1 => 'Plumbing',
        2 => 'Painting',
        3 => 'Electrical',
        4 => 'Cleaning',
        5 => 'Carpentry',
        6 => 'Others',
    ];

    public const PAYMENT_METHOD = [
        1 => 'Cash',
        2 => 'Credit Card',
        3 => 'Debit Card',
        4 => 'Bank Transfer',
        5 => 'Other',
    ];

    public const PAYMENT_STATUS = [
        1 => 'Not Paid',
        2 => 'Partially Paid',
        3 => 'Fully Paid',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(JobOrderDetail::class);
    }

    public function dailyWorkLogs(): HasMany
    {
        return $this->hasMany(JobOrderDailyWorkLog::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccountHolder::class, 'customer_id');
    }

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class, 'job_site_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Pending';
    }

    public function getJobTypeLabelAttribute(): ?string
    {
        return $this->job_type ? (self::JOB_TYPE[$this->job_type] ?? null) : null;
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return $this->payment_method ? (self::PAYMENT_METHOD[$this->payment_method] ?? null) : null;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS[$this->payment_status] ?? 'Not Paid';
    }
}
