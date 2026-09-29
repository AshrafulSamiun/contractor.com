<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'customer_details' => 'array', 'items' => 'array',
        'invoice_date' => 'date', 'due_date' => 'date', 'delivery_date' => 'date', 'job_order_date' => 'date',
        'exchange_rate' => 'decimal:6', 'subtotal' => 'decimal:2',
        'discount' => 'decimal:2', 'sales_tax' => 'decimal:2',
        'total' => 'decimal:2', 'paid' => 'decimal:2',
    ];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function payments() { return $this->hasMany(SalesInvoicePayment::class); }
    public function returns() { return $this->hasMany(SalesReturn::class); }
    public function creditNotes() { return $this->hasMany(CustomerCreditNote::class); }
}
