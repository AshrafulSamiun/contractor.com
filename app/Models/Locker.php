<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Locker extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'locker_name',
        'locker_type',
        'facility_name',
        'location',
        'status',
        'capacity',
        'notes',
        'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'capacity' => 'integer',
    ];

    public const TYPE_FACILITY_OWNED = 'facility_owned';
    public const TYPE_THIRD_PARTY_OWNED = 'third_party_owned';

    public function compartments(): HasMany
    {
        return $this->hasMany(LockerCompartment::class);
    }
}
