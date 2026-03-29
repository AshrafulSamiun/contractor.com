<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockerAccess extends Model
{
    protected $fillable = [
        'user_id',
        'locker_id',
        'recipient_id',
        'access_type',
        'status',
        'starts_at',
        'ends_at',
        'last_used_at',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }
}
