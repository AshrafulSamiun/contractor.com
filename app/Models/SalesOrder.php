<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'customer_details' => 'array', 'items' => 'array', 'order_date' => 'date', 'valid_until' => 'date',
        'expected_delivery_date' => 'date', 'exchange_rate' => 'decimal:6', 'subtotal' => 'decimal:2',
        'discount' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2',
    ];
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function customer() { return $this->belongsTo(AccountHolder::class, 'customer_id'); }
    public function estimation() { return $this->belongsTo(Estimation::class); }
}
