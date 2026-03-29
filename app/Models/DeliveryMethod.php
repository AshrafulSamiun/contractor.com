<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method_name',
        'method_type',
        'is_active',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
