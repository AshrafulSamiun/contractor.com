<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ProjectScoped;

class InvoiceTerm extends Model
{
    use ProjectScoped;
    protected $fillable = [
        'project_id',
        'term_id',
        'term_name',
        'description',
        'note',
        'status',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'term_id' => 'string',
        'term_name' => 'string',
        'description' => 'string',
        'note' => 'string',
        'status' => 'integer',
    ];
}
