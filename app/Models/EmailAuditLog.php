<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_message_id',
        'user_id',
        'status',
        'error',
    ];
}
