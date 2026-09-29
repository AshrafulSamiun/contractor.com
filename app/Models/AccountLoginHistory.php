<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLoginHistory extends Model
{
    protected $fillable = ['project_id', 'user_id', 'device_id', 'device_name', 'browser', 'ip_address', 'location', 'result', 'logged_in_at'];
    protected $casts = ['project_id' => 'integer', 'user_id' => 'integer', 'device_id' => 'integer', 'logged_in_at' => 'datetime'];
}
