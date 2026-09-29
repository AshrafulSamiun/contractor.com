<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerDebitNoteSettlement extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['settlement_date' => 'date', 'amount' => 'decimal:2'];
}
