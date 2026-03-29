<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'permission_id',
        'allowed',
        'granted_by_user_id',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];
}
