<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facility_name',
        'facility_type',
        'unit_no',
        'street',
        'city',
        'state',
        'postal_code',
        'country_id',
        'country',
        'office_phone',
        'mobile_phone',
        'email',
        'fax',
        'website',
        'status',
    ];

    protected $casts = [
        'country_id' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
