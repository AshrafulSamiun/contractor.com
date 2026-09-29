<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes;

    public const STATUSES = ['Active', 'Under Maintenance', 'Disposed'];

    protected $fillable = [
        'project_id', 'asset_no', 'asset_name', 'asset_group', 'asset_type', 'location',
        'status', 'purchase_date', 'purchase_cost', 'book_value', 'last_valuation_date',
        'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date', 'last_valuation_date' => 'date',
        'purchase_cost' => 'decimal:2', 'book_value' => 'decimal:2',
    ];
}
