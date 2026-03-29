<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_plan',
        'to_plan',
        'changed_by_user_id',
    ];
}
