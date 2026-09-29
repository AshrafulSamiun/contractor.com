<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetupProfessionalLicense extends Model
{
    protected $fillable = [
        'account_setup_id',
        'license_name',
        'license_number',
        'legal_business_name',
        'license_type',
        'issuing_country_id',
        'issuing_country',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'license_status',
        'license_website',
        'verification_url',
        'description_scope',
        'sort_order',
    ];

    protected $casts = [
        'issuing_country_id' => 'integer',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'sort_order' => 'integer',
    ];

    public function accountSetup()
    {
        return $this->belongsTo(AccountSetup::class);
    }

    public function countryRef()
    {
        return $this->belongsTo(Country::class, 'issuing_country_id');
    }
}
