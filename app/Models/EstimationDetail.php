<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimationDetail extends Model
{
    use HasFactory;

    protected $table = 'estimation_details';

    protected $fillable = [
        'estimation_id',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'sale_tax_percentage',
        'sale_tax_amount',
        'total',
    ];

    protected $casts = [
        'estimation_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'sale_tax_percentage' => 'decimal:2',
        'sale_tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class, 'estimation_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $quantity = (float) $model->quantity;
            $unitPrice = (float) $model->unit_price;
            $taxPercentage = (float) $model->sale_tax_percentage;

            $subtotal = $quantity * $unitPrice;
            $taxAmount = $subtotal * ($taxPercentage / 100);
            $model->sale_tax_amount = $taxAmount;
            $model->total = $subtotal + $taxAmount;
        });
    }
}
