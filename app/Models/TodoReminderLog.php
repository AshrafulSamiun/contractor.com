<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'todo_task_id',
        'user_id',
        'reminder_slot',
        'remind_at',
        'sent_at',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
