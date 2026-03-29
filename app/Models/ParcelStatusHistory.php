<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcel_id',
        'from_status',
        'to_status',
        'note',
    ];
}
