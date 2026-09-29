<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetup extends Model
{
    protected $appends = [
        'full_name',
        'business_registration_number',
        'trade_service_type',
        'expiry_date',
        'preferred_contact_method',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'operating_name',
        'tax_number',
        'year_established',
        'business_structure',
        'company_description',
        'profile_date_of_birth',
        'profile_nationality',
        'company_address_line_2',
        'linkedin_profile',
        'step_2_confirmation_ack',
        'insurance_company_name',
        'insurance_policy_number',
        'insurance_legal_business_name',
        'insurance_coverage_type',
        'insurance_policy_start_date',
        'insurance_policy_end_date',
        'insurance_coverage_amount',
        'insurance_deductible_amount',
        'insurance_issuing_country_id',
        'insurance_issuing_country',
        'insurance_issuing_authority',
        'insurance_certificate_number',
        'insurance_issue_date',
        'insurance_description',
        'insurance_additional_insured',
        'insurance_certificate_path',
        'insurance_certificate_url',
        'insurance_certificate_name',
        'business_suite',
        'business_contact_name',
        'business_contact_phone',
        'business_contact_email',
        'accounts_payable_name',
        'accounts_payable_phone',
        'accounts_payable_email',
        'director_owner_name',
        'director_owner_phone',
        'director_owner_email',
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
        'company_id_number',
        'business_number',
        'incorporation_date',
        'company_status',
        'business_type',
        'currency_code',
        'business_location',
        'fax_number',
        'facebook_profile',
        'instagram_profile',
        'industry_type',
        'years_in_business',
        'number_of_employees',
        'annual_revenue_range',
        'hear_about_source',
        'owner_name',
        'director_name',
        'authorized_contact_name',
        'designation_title',
        'authorized_contact_phone',
        'authorized_contact_email',
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
        'contact_primary_phone',
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
        'security_credentials_expires_at',
        'mfa_email',
        'mfa_phone',
        'mfa_verified_at',
        'declaration_ack',
        'declaration_full_name',
        'declaration_position_title',
        'declaration_signed_at',
        'declaration_initials',
        'declaration_signature',
        'preview_confirm_ack',
        'preview_full_name',
        'preview_position_title',
        'preview_initials',
        'preview_signature',
        'subscription_plan',
        'license_ack',
        'billing_method',
        'billing_address',
        'billing_cycle',
        'payment_schedule_start_date',
        'payment_schedule_ack',
        'payment_schedule_acknowledged_at',
        'primary_cardholder_name',
        'primary_cardholder_kind',
        'primary_card_last_four',
        'primary_card_type',
        'primary_card_expiry',
        'backup_cardholder_name',
        'backup_cardholder_kind',
        'backup_card_last_four',
        'backup_card_type',
        'backup_card_expiry',
        'payment_method_ack',
        'payment_method_acknowledged_at',
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
        'activation_status',
        'activation_initiated_at',
        'activation_completed_at',
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
        'step_8_done',
        'step_9_done',
        'step_10_done',
        'step_11_done',
        'step_12_done',
        'step_13_done',
        'step_14_done',
        'step_15_done',
        'step_16_done',
        'step_17_done',
        'step_18_done',

        'data',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'incorporation_date' => 'date',
        'admin_created_date' => 'date',
        'payment_schedule_start_date' => 'date',
        'payment_schedule_ack' => 'boolean',
        'payment_schedule_acknowledged_at' => 'datetime',
        'payment_method_ack' => 'boolean',
        'payment_method_acknowledged_at' => 'datetime',
        'company_country_id' => 'integer',
        'facility_country_id' => 'integer',
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'notify_arrival' => 'boolean',
        'notify_security' => 'boolean',
        'notify_marketing' => 'boolean',
        'security_2fa' => 'boolean',
        'security_policy_ack' => 'boolean',
        'security_credentials_expires_at' => 'datetime',
        'mfa_verified_at' => 'datetime',
        'declaration_ack' => 'boolean',
        'preview_confirm_ack' => 'boolean',
        'declaration_signed_at' => 'date',
        'license_ack' => 'boolean',
        'billing_auto_renew' => 'boolean',
        'billing_policy_ack' => 'boolean',
        'admin_is_system' => 'boolean',
        'captcha_ack' => 'boolean',
        'activation_initiated_at' => 'datetime',
        'activation_completed_at' => 'datetime',
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
        'step_8_done' => 'boolean',
        'step_9_done' => 'boolean',
        'step_10_done' => 'boolean',
        'step_11_done' => 'boolean',
        'step_12_done' => 'boolean',
        'step_13_done' => 'boolean',
        'step_14_done' => 'boolean',
        'step_15_done' => 'boolean',
        'step_16_done' => 'boolean',
        'step_17_done' => 'boolean',
        'step_18_done' => 'boolean',

        'data' => 'array',
    ];

    public function facilityUsage()
    {
        return $this->hasMany(AccountSetupFacility::class);
    }

    public function professionalLicenses()
    {
        return $this->hasMany(AccountSetupProfessionalLicense::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function setupContacts()
    {
        return $this->hasMany(AccountSetupContact::class)->orderBy('sort_order');
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

    public function getExpiryDateAttribute(): ?string
    {
        return $this->getSetupFormValue('expiry_date');
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

    public function getPreferredContactMethodAttribute(): ?string
    {
        return $this->getSetupFormValue('preferred_contact_method');
    }

    public function getEmergencyContactNameAttribute(): ?string
    {
        return $this->getSetupFormValue('emergency_contact_name');
    }

    public function getEmergencyContactPhoneAttribute(): ?string
    {
        return $this->getSetupFormValue('emergency_contact_phone');
    }

    public function getEmergencyContactRelationshipAttribute(): ?string
    {
        return $this->getSetupFormValue('emergency_contact_relationship');
    }

    public function getOperatingNameAttribute(): ?string
    {
        return $this->getSetupFormValue('operating_name');
    }

    public function getTaxNumberAttribute(): ?string
    {
        return $this->getSetupFormValue('tax_number');
    }

    public function getYearEstablishedAttribute(): ?string
    {
        return $this->getSetupFormValue('year_established');
    }

    public function getBusinessStructureAttribute(): ?string
    {
        return $this->getSetupFormValue('business_structure');
    }

    public function getCompanyDescriptionAttribute(): ?string
    {
        return $this->getSetupFormValue('company_description');
    }

    public function getProfileDateOfBirthAttribute(): ?string
    {
        return $this->getSetupFormValue('profile_date_of_birth');
    }

    public function getProfileNationalityAttribute(): ?string
    {
        return $this->getSetupFormValue('profile_nationality');
    }

    public function getCompanyAddressLine2Attribute(): ?string
    {
        return $this->getSetupFormValue('company_address_line_2');
    }

    public function getLinkedinProfileAttribute(): ?string
    {
        return $this->getSetupFormValue('linkedin_profile');
    }

    public function getStep2ConfirmationAckAttribute(): ?string
    {
        return $this->getSetupFormValue('step_2_confirmation_ack');
    }

    public function getInsuranceCompanyNameAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_company_name');
    }

    public function getInsurancePolicyNumberAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_policy_number');
    }

    public function getInsuranceLegalBusinessNameAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_legal_business_name');
    }

    public function getInsuranceCoverageTypeAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_coverage_type');
    }

    public function getInsurancePolicyStartDateAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_policy_start_date');
    }

    public function getInsurancePolicyEndDateAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_policy_end_date');
    }

    public function getInsuranceCoverageAmountAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_coverage_amount');
    }

    public function getInsuranceDeductibleAmountAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_deductible_amount');
    }

    public function getInsuranceIssuingCountryIdAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_issuing_country_id');
    }

    public function getInsuranceIssuingCountryAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_issuing_country');
    }

    public function getInsuranceIssuingAuthorityAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_issuing_authority');
    }

    public function getInsuranceCertificateNumberAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_certificate_number');
    }

    public function getInsuranceIssueDateAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_issue_date');
    }

    public function getInsuranceDescriptionAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_description');
    }

    public function getInsuranceAdditionalInsuredAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_additional_insured');
    }

    public function getInsuranceCertificatePathAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_certificate_path');
    }

    public function getInsuranceCertificateUrlAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_certificate_url');
    }

    public function getInsuranceCertificateNameAttribute(): ?string
    {
        return $this->getSetupFormValue('insurance_certificate_name');
    }

    public function getBusinessSuiteAttribute(): ?string
    {
        return $this->getSetupFormValue('business_suite');
    }

    public function getBusinessContactNameAttribute(): ?string
    {
        return $this->getSetupFormValue('business_contact_name');
    }

    public function getBusinessContactPhoneAttribute(): ?string
    {
        return $this->getSetupFormValue('business_contact_phone');
    }

    public function getBusinessContactEmailAttribute(): ?string
    {
        return $this->getSetupFormValue('business_contact_email');
    }

    public function getAccountsPayableNameAttribute(): ?string
    {
        return $this->getSetupFormValue('accounts_payable_name');
    }

    public function getAccountsPayablePhoneAttribute(): ?string
    {
        return $this->getSetupFormValue('accounts_payable_phone');
    }

    public function getAccountsPayableEmailAttribute(): ?string
    {
        return $this->getSetupFormValue('accounts_payable_email');
    }

    public function getDirectorOwnerNameAttribute(): ?string
    {
        return $this->getSetupFormValue('director_owner_name');
    }

    public function getDirectorOwnerPhoneAttribute(): ?string
    {
        return $this->getSetupFormValue('director_owner_phone');
    }

    public function getDirectorOwnerEmailAttribute(): ?string
    {
        return $this->getSetupFormValue('director_owner_email');
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
