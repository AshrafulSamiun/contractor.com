<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetupFacility extends Model
{
    protected $fillable = [
        'account_setup_id',
        'name',
        'part',
        'city',
        'country_id',
        'country',
        'assigned',
        'status',
    ];

    protected $casts = [
        'country_id' => 'integer',
        'assigned' => 'integer',
    ];

    public function accountSetup()
    {
        return $this->belongsTo(AccountSetup::class);
    }

    public function countryRef()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
