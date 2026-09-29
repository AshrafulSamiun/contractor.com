<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'seller_name',
        'contact_person',
        'address',
        'tax_number',
        'vendor_category',
        'email',
        'phone',
        'website',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'user_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
