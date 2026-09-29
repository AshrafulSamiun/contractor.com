<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'payment_date' => 'date', 'discount_date' => 'date', 'posted_at' => 'datetime',
        'amount' => 'decimal:2', 'write_off' => 'decimal:2', 'take_discount' => 'boolean', 'group_by_invoice' => 'boolean',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function seller() { return $this->belongsTo(Seller::class); }
    public function allocations() { return $this->hasMany(BillPaymentAllocation::class); }
}
