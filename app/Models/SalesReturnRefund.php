<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnRefund extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['refund_date' => 'date', 'amount' => 'decimal:2'];
}
