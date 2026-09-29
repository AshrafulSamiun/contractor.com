<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_order_id',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'sale_tax_percentage',
        'sale_tax_amount',
        'total',
    ];

    protected $casts = [
        'job_order_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'sale_tax_percentage' => 'decimal:2',
        'sale_tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            $subtotal = (float) $model->quantity * (float) $model->unit_price;
            $taxAmount = $subtotal * (((float) $model->sale_tax_percentage) / 100);
            $model->sale_tax_amount = $taxAmount;
            $model->total = $subtotal + $taxAmount;
        });
    }
}
