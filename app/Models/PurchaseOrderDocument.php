<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDocument extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2', 'sales_tax' => 'decimal:2', 'total' => 'decimal:2',
        'items' => 'array',
    ];
}
