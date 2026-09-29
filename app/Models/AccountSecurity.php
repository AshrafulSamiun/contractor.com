<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSecurity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'user_id', 'mfa_enabled', 'verification_method',
        'password_changed_at', 'pin_changed_at', 'registered_devices',
        'active_sessions', 'login_history', 'inserted_by', 'updated_by',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'user_id' => 'integer',
        'mfa_enabled' => 'boolean',
        'password_changed_at' => 'datetime',
        'pin_changed_at' => 'datetime',
        'registered_devices' => 'array',
        'active_sessions' => 'array',
        'login_history' => 'array',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
