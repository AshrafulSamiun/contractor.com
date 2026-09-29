<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'currency_code',
        'currency_name',
        'currency_symbol',
        'status_active',
        'inserted_by',
        'updated_by',
    ];

    protected $casts = [
        'status_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];
}
