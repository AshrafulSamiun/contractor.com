<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'company_no', 'company_name', 'company_type', 'insurance_type',
        'policy_no', 'policy_status', 'balance', 'expiry_date',
        'status_active', 'agent_broker_name', 'agent_broker_phone', 'agent_broker_email',
        'primary_contact_name', 'primary_contact_phone', 'primary_contact_email',
        'street_address', 'city', 'state_province', 'postal_code', 'country_id', 'notes',
        'created_by', 'updated_by',
    ];

    protected $casts = ['status_active' => 'boolean', 'balance' => 'decimal:2', 'expiry_date' => 'date'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
