<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quotations';

    protected $fillable = [
        'estimation_id',
        'quotation_no',
        'issue_date',
        'expire_date',
        'currency',
        'status',
        'job_type',
        'job_description',
        'customer_id',
        'job_site_id',
        'schedule_start_date',
        'schedule_end_date',
        'scope_of_work',
        'sub_total',
        'tax',
        'discount',
        'total',
        'note',
        'payment_method',
        'deposit_required',
        'payment_term',
        'customer_approval_date',
        'customer_approve_by',
        'approval_method',
        'convert_to_job_order',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expire_date' => 'datetime',
        'schedule_start_date' => 'datetime',
        'schedule_end_date' => 'datetime',
        'customer_approval_date' => 'datetime',
        'currency' => 'integer',
        'status' => 'integer',
        'job_type' => 'integer',
        'customer_id' => 'integer',
        'job_site_id' => 'integer',
        'sub_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_method' => 'integer',
        'deposit_required' => 'boolean',
        'approval_method' => 'integer',
        'convert_to_job_order' => 'boolean',
    ];

    const CURRENCY = [
        1 => 'USD',
        2 => 'EUR',
        3 => 'GBP',
        4 => 'BDT',
    ];

    const STATUS = [
        1 => 'Pending',
        2 => 'Approved',
        3 => 'Cancelled',
        4 => 'Expired',
    ];

    const JOB_TYPE = [
        1 => 'Plumbing',
        2 => 'Painting',
        3 => 'Electrical',
        4 => 'Cleaning',
        5 => 'Carpentry',
        6 => 'Others',
    ];

    const PAYMENT_METHOD = [
        1 => 'Cash',
        2 => 'Credit Card',
        3 => 'Debit Card',
        4 => 'Bank Transfer',
        5 => 'Others',
    ];

    const APPROVAL_METHOD = [
        1 => 'Phone Call',
        2 => 'Text Message',
        3 => 'Email',
        4 => 'In Person',
        5 => 'Mail',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id');
    }

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class, 'estimation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccountHolder::class, 'customer_id');
    }

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class, 'job_site_id');
    }

    public function getCurrencyLabelAttribute(): string
    {
        return self::CURRENCY[$this->currency] ?? 'USD';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Pending';
    }

    public function getJobTypeLabelAttribute(): string
    {
        return self::JOB_TYPE[$this->job_type] ?? 'Others';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHOD[$this->payment_method] ?? 'Cash';
    }

    public function getApprovalMethodLabelAttribute(): string
    {
        return self::APPROVAL_METHOD[$this->approval_method] ?? 'Email';
    }
}
