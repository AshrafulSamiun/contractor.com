<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'return_date' => 'date', 'return_due_date' => 'date', 'items' => 'array',
        'subtotal' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2', 'refunded' => 'decimal:2',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function purchaseInvoice() { return $this->belongsTo(PurchaseInvoice::class); }
    public function refunds() { return $this->hasMany(PurchaseReturnRefund::class); }
}
