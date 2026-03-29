<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facility_name',
        'recipient_name',
        'status',
        'location',
        'tracking_code',
        'notes',
        'received_at',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
}
