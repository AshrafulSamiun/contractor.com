<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSecuritySession extends Model
{
    protected $fillable = ['project_id', 'user_id', 'device_id', 'token_id', 'ip_address', 'started_at', 'last_active_at', 'logged_out_at'];
    protected $casts = ['project_id' => 'integer', 'user_id' => 'integer', 'device_id' => 'integer', 'token_id' => 'integer', 'started_at' => 'datetime', 'last_active_at' => 'datetime', 'logged_out_at' => 'datetime'];
    public function device() { return $this->belongsTo(AccountSecurityDevice::class, 'device_id'); }
}
