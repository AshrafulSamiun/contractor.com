<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSecurityDevice extends Model
{
    protected $fillable = ['project_id', 'user_id', 'device_key', 'device_name', 'device_type', 'browser', 'user_agent', 'ip_address', 'location', 'registered_at', 'last_seen_at', 'revoked_at'];
    protected $casts = ['project_id' => 'integer', 'user_id' => 'integer', 'registered_at' => 'datetime', 'last_seen_at' => 'datetime', 'revoked_at' => 'datetime'];
}
