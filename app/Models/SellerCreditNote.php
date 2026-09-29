<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerCreditNote extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'credit_note_date' => 'date', 'due_date' => 'date', 'items' => 'array',
        'subtotal' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function purchaseInvoice() { return $this->belongsTo(PurchaseInvoice::class); }
}
