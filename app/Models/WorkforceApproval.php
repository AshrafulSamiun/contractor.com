<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'status',
        'step',
        'approved_by',
        'actor_role',
        'approved_at',
        'note',
        'meta',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'meta' => 'array',
    ];
}
