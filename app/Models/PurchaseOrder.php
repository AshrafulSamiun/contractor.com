<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'project_id', 'created_by', 'po_no', 'seller_no', 'seller_name', 'seller_details',
        'requested_by', 'department', 'project', 'po_date', 'expiry_at', 'expected_delivery_at',
        'delivery_location', 'delivery_details', 'currency_code', 'status', 'payment_status',
        'payment_term', 'payment_method', 'subtotal', 'sales_tax', 'total', 'items', 'terms', 'notes',
        'seller_id', 'requester_details', 'approval_status',
    ];

    protected $casts = [
        'seller_details' => 'array', 'delivery_details' => 'array', 'items' => 'array',
        'po_date' => 'date', 'expiry_at' => 'datetime', 'expected_delivery_at' => 'datetime',
        'subtotal' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2',
        'project_id' => 'integer', 'seller_id' => 'integer', 'requester_details' => 'array',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function documents() { return $this->hasMany(PurchaseOrderDocument::class); }
    public function purchaseInvoice() { return $this->hasOne(PurchaseInvoice::class); }
}
