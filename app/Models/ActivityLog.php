<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_user_id',
        'module',
        'action',
        'description',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];
}
