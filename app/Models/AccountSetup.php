<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetup extends Model
{
    protected $appends = [
        'full_name',
        'business_registration_number',
        'trade_service_type',
        // 'registration_country',
        // 'registration_province',
        // 'registration_notes',
        // 'contact_full_name',
    ];

    protected $hidden = [
        'security_password_hash',
        'security_pin_hash',
    ];

    protected $fillable = [
        'user_id',
        'current_step',
        'completed_at',
        'company_name',
        'company_logo_url',
        'company_logo_path',
        'company_address',
        'company_city',
        'company_state',
        'company_zip',
        'company_country_id',
        'company_country',
        'company_phone',
        'registration_country',
        'registration_province',
        'facility_name',
        'facility_part_number',
        'facility_address',
        'facility_city',
        'facility_state',
        'facility_zip',
        'facility_country_id',
        'facility_country',
        'facility_phone',
        'facility_email',
        'contact_mobile_phone',
        'contact_business_email',
        'contact_alt_email',
        'contact_website',
        'pref_timezone',
        'pref_language',
        'pref_date_format',
        'notify_email',
        'notify_sms',
        'notify_arrival',
        'notify_security',
        'notify_marketing',
        'security_username',
        'security_password_hash',
        'security_pin_hash',
        'security_2fa',
        'security_policy_ack',
        'subscription_plan',
        'license_ack',
        'billing_method',
        'billing_address',
        'billing_cycle',
        'billing_auto_renew',
        'billing_policy_ack',
        'admin_first_name',
        'admin_last_name',
        'admin_role',
        'admin_is_system',
        'admin_phone',
        'admin_email',
        'admin_created_date',
        'recovery_email',
        'recovery_phone',
        'email_verify_status',
        'captcha_ack',
        'safety_ack',
        'safety_ack_at',
        'safety_ack_ip',
        'safety_ack_user_agent',
        'safety_policy_version',
        'terms_ack',
        'privacy_ack',
        'final_ack',
        'step_1_done',
        'step_2_done',
        'step_3_done',
        'step_4_done',
        'step_5_done',
        'step_6_done',
        'step_7_done',

        'data',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'admin_created_date' => 'date',
        'company_country_id' => 'integer',
        'facility_country_id' => 'integer',
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'notify_arrival' => 'boolean',
        'notify_security' => 'boolean',
        'notify_marketing' => 'boolean',
        'security_2fa' => 'boolean',
        'security_policy_ack' => 'boolean',
        'license_ack' => 'boolean',
        'billing_auto_renew' => 'boolean',
        'billing_policy_ack' => 'boolean',
        'admin_is_system' => 'boolean',
        'captcha_ack' => 'boolean',
        'safety_ack' => 'boolean',
        'safety_ack_at' => 'datetime',
        'terms_ack' => 'boolean',
        'privacy_ack' => 'boolean',
        'final_ack' => 'boolean',
        'step_1_done' => 'boolean',
        'step_2_done' => 'boolean',
        'step_3_done' => 'boolean',
        'step_4_done' => 'boolean',
        'step_5_done' => 'boolean',
        'step_6_done' => 'boolean',
        'step_7_done' => 'boolean',

        'data' => 'array',
    ];

    public function facilityUsage()
    {
        return $this->hasMany(AccountSetupFacility::class);
    }

    public function companyCountry()
    {
        return $this->belongsTo(Country::class, 'company_country_id');
    }

    public function facilityCountry()
    {
        return $this->belongsTo(Country::class, 'facility_country_id');
    }

    public function getFullNameAttribute(): ?string
    {
        return $this->getSetupFormValue('full_name');
    }

    public function getBusinessRegistrationNumberAttribute(): ?string
    {
        return $this->getSetupFormValue('business_registration_number');
    }

    public function getTradeServiceTypeAttribute(): ?string
    {
        return $this->getSetupFormValue('trade_service_type');
    }

    public function getRegistrationAuthorityAttribute(): ?string
    {
        return $this->getSetupFormValue('registration_authority');
    }

    public function getRegistrationIssueDateAttribute(): ?string
    {
        return $this->getSetupFormValue('registration_issue_date');
    }

    public function getRegistrationNotesAttribute(): ?string
    {
        return $this->getSetupFormValue('registration_notes');
    }

    public function getContactFullNameAttribute(): ?string
    {
        return $this->getSetupFormValue('contact_full_name');
    }

    protected function getSetupFormValue(string $key): ?string
    {
        $data = $this->data;
        if (!is_array($data)) {
            return null;
        }

        $setupForm = $data['setup_form'] ?? null;
        if (!is_array($setupForm) || !array_key_exists($key, $setupForm)) {
            return null;
        }

        $value = $setupForm[$key];

        return is_scalar($value) ? (string) $value : null;
    }
}
