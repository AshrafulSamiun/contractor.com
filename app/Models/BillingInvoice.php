<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'status',
        'currency',
        'amount_due',
        'amount_paid',
        'amount_remaining',
        'period_start',
        'period_end',
        'hosted_invoice_url',
        'invoice_pdf',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];
}
