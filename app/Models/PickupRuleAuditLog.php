<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupRuleAuditLog extends Model
{
    protected $fillable = [
        'pickup_rule_id',
        'user_id',
        'action',
        'before_json',
        'after_json',
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
    ];
}
