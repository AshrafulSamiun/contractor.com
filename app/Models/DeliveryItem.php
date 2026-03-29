<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
        'categories',
        'is_active',
        'note',
    ];

    protected $casts = [
        'categories' => 'array',
        'is_active' => 'boolean',
    ];
}
