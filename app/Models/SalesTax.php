<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ProjectScoped;

class SalesTax extends Model
{
    use ProjectScoped;
    protected $fillable = [
        'project_id',
        'system_prefix',
        'system_no',
        'tax_name',
        'tax_type',
        'tax_rate',
        'application_reason',
        'status_active',
        'notes',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'system_prefix' => 'string',
        'tax_type' => 'integer',
        'tax_rate' => 'decimal:4',
        'application_reason' => 'integer',
        'status_active' => 'boolean',
    ];
}
