<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ProjectScoped;

class PaymentMethod extends Model
{
    use ProjectScoped;
    protected $fillable = [
        'project_id',
        'system_prefix',
        'system_no',
        'name',
        'payment_type',
        'linked_account',
        'status_active',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'system_prefix' => 'string',
        'payment_type' => 'integer',
        'status_active' => 'boolean',
    ];
}
