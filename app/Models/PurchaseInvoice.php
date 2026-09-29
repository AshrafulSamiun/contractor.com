<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'seller_details' => 'array', 'requester_details' => 'array', 'delivery_details' => 'array',
        'items' => 'array', 'invoice_date' => 'date', 'due_date' => 'date', 'delivery_date' => 'date',
        'subtotal' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2', 'paid' => 'decimal:2',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function payments() { return $this->hasMany(PurchaseInvoicePayment::class); }
    public function returns() { return $this->hasMany(PurchaseReturn::class); }
    public function debitNotes() { return $this->hasMany(SellerDebitNote::class); }
    public function creditNotes() { return $this->hasMany(SellerCreditNote::class); }
}
