<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'return_date' => 'date', 'expected_pickup_date' => 'date', 'items' => 'array',
        'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'sales_tax' => 'decimal:2',
        'total' => 'decimal:2', 'refunded' => 'decimal:2',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function refunds() { return $this->hasMany(SalesReturnRefund::class); }
}
