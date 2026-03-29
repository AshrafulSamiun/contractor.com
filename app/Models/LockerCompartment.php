<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerCompartment extends Model
{
    protected $fillable = [
        'user_id',
        'locker_id',
        'size_category',
        'compartment_code',
        'quantity',
        'activation_status',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class);
    }
}
