<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoicePayment extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];
}
