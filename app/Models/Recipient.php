<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'recipient_types',
        'facility_id',
        'facility_name',
        'floor_no',
        'residential_suite_no',
        'commercial_unit_no',
        'office',
        'store',
        'phone',
        'email',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'recipient_types' => 'array',
        'facility_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
