<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\AccountSetupContact;
use App\Models\Country;
use App\Models\AccountSetupProfessionalLicense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountSetupController extends Controller
{
    private const TOTAL_STEPS = 18;

    protected array $stepFieldMap = [
        1 => [
            'company_name',
            'admin_first_name',
            'admin_last_name',
            'admin_role',
            'contact_primary_phone',
            'contact_mobile_phone',
            'contact_business_email',
        ],
        2 => [
            'company_logo_url',
            'company_logo_path',
            'admin_role',
            'admin_first_name',
            'admin_last_name',
            'contact_primary_phone',
            'contact_business_email',
            'contact_website',
            'company_address',
            'company_city',
            'company_state',
            'company_zip',
            'company_country_id',
            'company_country',
            'pref_language',
        ],
        3 => [
            'company_id_number',
            'business_number',
            'incorporation_date',
            'company_status',
            'business_type',
            'currency_code',
            'company_address',
            'company_city',
            'company_state',
            'company_zip',
            'company_country_id',
            'company_country',
            'business_location',
            'contact_business_email',
            'contact_alt_email',
            'contact_website',
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
            'contact_primary_phone',
        ],
        4 => [],
        5 => [],
        6 => [],
        7 => [],
        8 => [
            'payment_schedule_start_date',
            'payment_schedule_ack',
            'payment_schedule_acknowledged_at',
        ],
        9 => [
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
        ],
        10 => [],
        11 => [
            'security_username',
            'security_password_hash',
            'security_pin_hash',
            'security_2fa',
            'security_policy_ack',
            'security_credentials_expires_at',
            'mfa_email',
            'mfa_phone',
            'mfa_verified_at',
            'contact_primary_phone',
            'recovery_phone',
            'contact_business_email',
            'recovery_email',
            'email_verify_status',
        ],
        12 => [
            'declaration_ack',
            'declaration_full_name',
            'declaration_position_title',
            'declaration_signed_at',
            'declaration_initials',
            'declaration_signature',
        ],
        13 => ['preview_confirm_ack', 'preview_full_name', 'preview_position_title', 'preview_initials', 'preview_signature'],
        14 => ['email_verify_status'],
        15 => ['activation_status', 'activation_initiated_at'],
        16 => [
            'safety_ack',
            'safety_ack_at',
            'safety_policy_version',
        ],
        17 => ['terms_ack', 'privacy_ack'],
        18 => ['final_ack'],
    ];

    protected array $dataFieldMap = [
        1 => [
            'full_name',
            'business_registration_number',
            'trade_service_type',
            'contact_full_name',
            'preferred_contact_method',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
        ],
        2 => [
            'full_name',
            'profile_date_of_birth',
            'profile_nationality',
            'company_address_line_2',
            'linkedin_profile',
            'step_2_confirmation_ack',
        ],
        3 => [
            'business_registration_number',
            'business_structure',
            'tax_number',
            'pst_qst_number',
            'company_description',
            'primary_services',
            'business_contact_name',
            'business_contact_phone',
            'business_contact_email',
            'director_owner_name',
        ],
        4 => [
            'professional_licenses',
        ],
        5 => [
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
        ],
        6 => ['subscription_plan', 'billing_cycle'],
        7 => [
            'additional_user_licenses',
            'user_licenses',
        ],
        8 => [
            'payment_schedule_items',
            'payment_schedule_service_label',
            'payment_schedule_summary_note',
            'payment_schedule_amount',
        ],
        9 => [],
        10 => ['setup_contacts'],
        11 => [],
        12 => [],
        13 => [],
        14 => [],
        15 => [],
        16 => ['safety_ack_ip', 'safety_ack_user_agent'],
        17 => [],
        18 => [],
    ];

    public function show(Request $request)
    {
        $user = $request->user();
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 1]
        );

        if ($this->isBlank($setup->company_name) && ! $this->isBlank($user->company_name)) {
            $setup->company_name = $user->company_name;
            $setup->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup, $user),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'step' => ['required', 'integer', 'min:1', 'max:'.self::TOTAL_STEPS],
            'data' => ['nullable', 'array'],
        ]);

        $step = (int) $validated['step'];
        $payload = $validated['data'] ?? [];
        $user = $request->user();
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 1]
        );

        $payload = $this->normalizePayload($step, $payload, $user, $setup);
        if ($step === 15 && $setup->activation_status !== 'active') {
            throw ValidationException::withMessages([
                'activation_status' => 'Your account must be activated by an administrator before you can continue.',
            ]);
        }
        $this->validateStepPayload($step, $payload, $user);

        $columnFields = $this->stepFieldMap[$step] ?? [];
        $dataFields = $this->dataFieldMap[$step] ?? [];
        $columns = array_intersect_key($payload, array_flip($columnFields));
        $extraData = array_intersect_key($payload, array_flip($dataFields));
        $userColumns = $this->extractUserColumns($step, $payload);

        $stepDone = 'step_'.$step.'_done';
        $jsonPayload = array_merge($columns, $extraData);

        DB::transaction(function () use ($setup, $columns, $jsonPayload, $step, $stepDone, $user, $userColumns, $payload) {
            if (! empty($columns)) {
                $setup->fill($columns);
            }

            $setup->current_step = max((int) $setup->current_step, $step);
            $setup->{$stepDone} = true;
            $this->syncJsonBackup($setup, $step, $jsonPayload, [
                ...$columns,
                'current_step' => $setup->current_step,
                $stepDone => true,
            ]);
            $setup->save();

            if ($step === 4) {
                $this->syncProfessionalLicenses(
                    $setup,
                    $payload['professional_licenses'] ?? [],
                );
            }

            if ($step === 10) {
                $this->syncSetupContacts(
                    $setup,
                    $payload['setup_contacts'] ?? [],
                );
            }

            if (! empty($userColumns)) {
                $user->forceFill($userColumns)->save();
            }
        });

        if (! empty($columns['subscription_plan'])) {
            $user->forceFill([
                'selected_plan' => $columns['subscription_plan'],
            ])->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup->fresh(), $user->fresh()),
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $validated = $request->validate([
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $validated['logo']->store('account-logos', 'public');
        $url = Storage::disk('public')->url($path);

        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );

        $setup->company_logo_path = $path;
        $setup->company_logo_url = $url;
        $this->syncJsonBackup($setup, max((int) $setup->current_step, 1), [
            'company_logo_path' => $path,
            'company_logo_url' => $url,
        ], [
            'company_logo_path' => $path,
            'company_logo_url' => $url,
        ]);
        $setup->save();

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'url' => $url,
            ],
        ]);
    }

    public function uploadInsuranceCertificate(Request $request)
    {
        $validated = $request->validate([
            'certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $path = $validated['certificate']->store('account-insurance-certificates', 'public');
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'url' => $url,
                'name' => $validated['certificate']->getClientOriginalName(),
            ],
        ]);
    }

    public function complete(Request $request)
    {
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );

        $user = $request->user();
        $this->assertSetupCanBeCompleted($setup, $user);

        $completedAt = now();
        $setup->current_step = self::TOTAL_STEPS;
        $setup->completed_at = $completedAt;
        $setup->final_ack = true;

        for ($i = 1; $i <= self::TOTAL_STEPS; $i += 1) {
            $stepDone = 'step_'.$i.'_done';
            $setup->{$stepDone} = true;
        }

        $this->syncJsonBackup($setup, self::TOTAL_STEPS, [
            'final_ack' => true,
            'completed_at' => $completedAt->toIso8601String(),
        ], [
            'current_step' => self::TOTAL_STEPS,
            'final_ack' => true,
            'completed_at' => $completedAt->toIso8601String(),
            'step_18_done' => true,
        ]);
        $setup->save();

        if ($setup->subscription_plan) {
            $user->forceFill([
                'selected_plan' => $setup->subscription_plan,
                'account_setup_completed_at' => $completedAt,
            ])->save();
        } else {
            $user->forceFill([
                'account_setup_completed_at' => $completedAt,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup->fresh(), $user->fresh()),
        ]);
    }

    protected function validateStepPayload(int $step, array $payload, User $user): void
    {
        $rules = match ($step) {
            1 => [
                'full_name' => ['required', 'string', 'max:255'],
                'admin_first_name' => ['required', 'string', 'max:120'],
                'admin_last_name' => ['required', 'string', 'max:120'],
                'admin_role' => ['required', 'string', 'max:120'],
                'company_name' => ['required', 'string', 'max:255'],
                'business_registration_number' => ['required', 'string', 'max:120'],
                'trade_service_type' => ['required', 'string', 'max:120'],
                'contact_primary_phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'contact_mobile_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'contact_business_email' => ['required', 'email', 'max:255'],
                'preferred_contact_method' => ['required', Rule::in(['phone', 'mobile', 'email'])],
                'emergency_contact_name' => ['nullable', 'string', 'max:120'],
                'emergency_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'emergency_contact_relationship' => ['nullable', 'string', 'max:120'],
            ],
            2 => [
                'full_name' => ['required', 'string', 'max:255'],
                'admin_role' => ['required', 'string', 'max:120'],
                'contact_primary_phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'contact_business_email' => ['required', 'email', 'max:255'],
                'profile_date_of_birth' => ['required', 'date', 'before:today'],
                'profile_nationality' => ['required', 'string', 'max:120'],
                'company_address' => ['required', 'string', 'max:255'],
                'company_address_line_2' => ['nullable', 'string', 'max:255'],
                'company_city' => ['required', 'string', 'max:120'],
                'company_state' => ['required', 'string', 'max:120'],
                'company_zip' => ['required', 'string', 'max:30'],
                'company_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
                'company_country' => ['nullable', 'string', 'max:120'],
                'pref_language' => ['required', 'string', 'max:40'],
                'linkedin_profile' => ['nullable', 'url', 'max:255'],
                'contact_website' => ['nullable', 'url', 'max:255'],
                'company_logo_url' => ['required', 'string', 'max:2048'],
                'company_logo_path' => ['required', 'string', 'max:255'],
                'step_2_confirmation_ack' => ['accepted'],
            ],
            3 => [
                'company_id_number' => ['required', 'string', 'max:40'],
                'company_name' => ['required', 'string', 'max:255'],
                'business_number' => ['required', 'string', 'max:120'],
                'business_registration_number' => ['required', 'string', 'max:120'],
                'incorporation_date' => ['nullable', 'date', 'before_or_equal:today'],
                'company_status' => ['required', Rule::in(['Active', 'Inactive', 'Pending', 'Suspended', 'Dissolved'])],
                'business_type' => ['required', Rule::in([
                    'Operate as a Company',
                    'Operate as an Individual',
                    'Operate as a Partnership',
                    'Operate as a Nonprofit',
                ])],
                'business_structure' => ['required', 'string', 'max:120'],
                'tax_number' => ['nullable', 'string', 'max:120'],
                'pst_qst_number' => ['nullable', 'string', 'max:120'],
                'currency_code' => ['required', 'string', 'max:10'],
                'company_city' => ['required', 'string', 'max:120'],
                'company_state' => ['required', 'string', 'max:120'],
                'company_zip' => ['required', 'string', 'max:30'],
                'company_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
                'company_country' => ['nullable', 'string', 'max:120'],
                'company_address' => ['required', 'string', 'max:255'],
                'business_location' => ['nullable', 'string', 'max:255'],
                'contact_primary_phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'contact_business_email' => ['required', 'email', 'max:255'],
                'contact_alt_email' => ['nullable', 'email', 'max:255'],
                'fax_number' => ['nullable', 'string', 'max:30'],
                'contact_website' => ['required', 'url', 'max:255'],
                'linkedin_profile' => ['nullable', 'url', 'max:255'],
                'facebook_profile' => ['nullable', 'url', 'max:255'],
                'instagram_profile' => ['nullable', 'url', 'max:255'],
                'industry_type' => ['required', 'string', 'max:120'],
                'primary_services' => ['required', 'array', 'min:1', 'max:6'],
                'primary_services.*' => ['required', 'string', 'max:120'],
                'years_in_business' => ['required', 'string', 'max:60'],
                'number_of_employees' => ['required', 'string', 'max:60'],
                'annual_revenue_range' => ['required', 'string', 'max:80'],
                'company_description' => ['required', 'string', 'max:1500'],
                'hear_about_source' => ['required', 'string', 'max:150'],
                'owner_name' => ['required', 'string', 'max:150'],
                'director_name' => ['nullable', 'string', 'max:150'],
                'authorized_contact_name' => ['required', 'string', 'max:150'],
                'designation_title' => ['required', 'string', 'max:120'],
                'authorized_contact_phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'authorized_contact_email' => ['required', 'email', 'max:255'],
            ],
            4 => [
                'professional_licenses' => ['required', 'array', 'min:1'],
                'professional_licenses.*.license_name' => ['required', 'string', 'max:150'],
                'professional_licenses.*.license_number' => ['required', 'string', 'max:150'],
                'professional_licenses.*.legal_business_name' => ['required', 'string', 'max:255'],
                'professional_licenses.*.license_type' => ['required', 'string', 'max:120'],
                'professional_licenses.*.issuing_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
                'professional_licenses.*.issuing_country' => ['nullable', 'string', 'max:120'],
                'professional_licenses.*.issuing_authority' => ['required', 'string', 'max:255'],
                'professional_licenses.*.issue_date' => ['required', 'date', 'before_or_equal:today'],
                'professional_licenses.*.expiry_date' => ['required', 'date'],
                'professional_licenses.*.license_status' => ['required', Rule::in(['Active', 'Pending Renewal', 'Expired', 'Suspended', 'Inactive'])],
                'professional_licenses.*.license_website' => ['required', 'url', 'max:255'],
                'professional_licenses.*.verification_url' => ['nullable', 'url', 'max:255'],
                'professional_licenses.*.description_scope' => ['nullable', 'string', 'max:1000'],
            ],
            5 => [
                'insurance_company_name' => ['required', 'string', 'max:255'],
                'insurance_policy_number' => ['required', 'string', 'max:120'],
                'insurance_legal_business_name' => ['required', 'string', 'max:255'],
                'insurance_coverage_type' => ['required', Rule::in([
                    'General Liability',
                    'Professional Liability',
                    'Workers Compensation',
                    'Commercial Auto',
                    'Umbrella Liability',
                    "Builder's Risk",
                    'Other',
                ])],
                'insurance_policy_start_date' => ['required', 'date'],
                'insurance_policy_end_date' => ['required', 'date', 'after_or_equal:insurance_policy_start_date'],
                'insurance_coverage_amount' => ['required', 'numeric', 'gt:0'],
                'insurance_deductible_amount' => ['nullable', 'numeric', 'min:0'],
                'insurance_issuing_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
                'insurance_issuing_country' => ['nullable', 'string', 'max:120'],
                'insurance_issuing_authority' => ['required', 'string', 'max:255'],
                'insurance_certificate_number' => ['required', 'string', 'max:120'],
                'insurance_issue_date' => ['required', 'date', 'before_or_equal:today'],
                'insurance_description' => ['nullable', 'string', 'max:1000'],
                'insurance_additional_insured' => ['nullable', 'string', 'max:255'],
                'insurance_certificate_path' => ['required', 'string', 'max:255'],
                'insurance_certificate_url' => ['required', 'string', 'max:2048'],
                'insurance_certificate_name' => ['required', 'string', 'max:255'],
            ],
            6 => [
                'subscription_plan' => ['required', Rule::in(['basic', 'pro', 'premium', 'enterprise'])],
                'billing_cycle' => ['required', Rule::in(['annually', 'monthly'])],
            ],
            7 => [
                'additional_user_licenses' => ['required', 'integer', 'min:1', 'max:500'],
                'user_licenses' => ['required', 'array', 'min:1', 'max:500'],
                'user_licenses.*.full_name' => ['required', 'string', 'max:150'],
                'user_licenses.*.phone_number' => ['required', 'string', 'max:40'],
                'user_licenses.*.license_number' => ['required', 'regex:/^UL-\d{6}-\d{4}$/'],
                'user_licenses.*.expiry_note' => ['nullable', 'string', 'max:120'],
            ],
            8 => [
                'payment_schedule_start_date' => ['required', 'date'],
                'payment_schedule_ack' => ['accepted'],
                'payment_schedule_items' => ['required', 'array', 'size:12'],
                'payment_schedule_items.*.payment_number' => ['required', 'integer', 'min:1', 'max:12'],
                'payment_schedule_items.*.service_period_start' => ['required', 'date'],
                'payment_schedule_items.*.service_period_end' => ['required', 'date'],
                'payment_schedule_items.*.auto_charge_date' => ['required', 'date'],
                'payment_schedule_items.*.charging_date' => ['required', 'date'],
                'payment_schedule_items.*.amount' => ['required', 'numeric', 'gte:0'],
                'payment_schedule_service_label' => ['nullable', 'string', 'max:120'],
                'payment_schedule_summary_note' => ['nullable', 'string', 'max:255'],
                'payment_schedule_amount' => ['nullable', 'numeric', 'gte:0'],
            ],
            9 => [
                'primary_cardholder_name' => ['required', 'string', 'max:150'],
                'primary_card_number' => ['required', 'regex:/^\d{13,19}$/'],
                'primary_card_expiry' => ['required', 'regex:/^\d{2}\s*\/\s*\d{4}$/'],
                'primary_card_cvv' => ['required', 'regex:/^\d{3,4}$/'],
                'backup_cardholder_name' => ['nullable', 'required_with:backup_card_number,backup_card_expiry,backup_card_cvv', 'string', 'max:150'],
                'backup_card_number' => ['nullable', 'required_with:backup_cardholder_name,backup_card_expiry,backup_card_cvv', 'regex:/^\d{13,19}$/'],
                'backup_card_expiry' => ['nullable', 'required_with:backup_cardholder_name,backup_card_number,backup_card_cvv', 'regex:/^\d{2}\s*\/\s*\d{4}$/'],
                'backup_card_cvv' => ['nullable', 'required_with:backup_cardholder_name,backup_card_number,backup_card_expiry', 'regex:/^\d{3,4}$/'],
                'payment_method_ack' => ['accepted'],
            ],
            10 => [
                'setup_contacts' => ['required', 'array', 'min:1', 'max:20'],
                'setup_contacts.*.department_name' => ['required', 'string', 'max:150'],
                'setup_contacts.*.contact_person' => ['required', 'string', 'max:150'],
                'setup_contacts.*.phone_number' => ['required', 'string', 'max:40'],
                'setup_contacts.*.email' => ['required', 'email', 'max:255'],
                'setup_contacts.*.position_title' => ['required', 'string', 'max:150'],
            ],
            11 => [
                'security_username' => ['required', 'email', 'max:255'],
                'security_password' => ['required', 'string', 'min:8', 'same:security_password_confirm'],
                'security_pin' => ['required', 'string', 'min:6', 'max:20', 'same:security_pin_confirm'],
                'security_pin_confirm' => ['required', 'string', 'min:6', 'max:20'],
                'contact_primary_phone' => ['required', 'string', 'max:40'],
                'recovery_phone' => ['required', 'string', 'max:40'],
                'contact_business_email' => ['required', 'email', 'max:255'],
                'recovery_email' => ['required', 'email', 'max:255', 'different:contact_business_email'],
            ],
            12 => [
                'declaration_ack' => ['accepted'],
                'declaration_full_name' => ['required', 'string', 'max:150'],
                'declaration_position_title' => ['required', 'string', 'max:150'],
                'declaration_signed_at' => ['required', 'date'],
                'declaration_initials' => ['required', 'string', 'max:10'],
                'declaration_signature' => ['required', 'string', 'max:150', 'same:declaration_full_name'],
            ],
            13 => [
                'preview_confirm_ack' => ['accepted'],
                'preview_full_name' => ['required', 'string', 'max:150'],
                'preview_position_title' => ['required', 'string', 'max:150'],
                'preview_initials' => ['required', 'string', 'max:10'],
                'preview_signature' => ['required', 'string', 'max:150', 'same:preview_full_name'],
            ],
            14 => [
                'email_verify_status' => ['nullable', Rule::in(['pending', 'verified'])],
            ],
            15 => [],
            16 => [
                'safety_ack' => ['accepted'],
            ],
            17 => [
                'terms_ack' => ['accepted'],
                'privacy_ack' => ['accepted'],
            ],
            default => [],
        };

        if (empty($rules)) {
            return;
        }

        validator($payload, $rules, [
            'contact_primary_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'contact_mobile_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ])->validate();

    }

    protected function normalizePayload(int $step, array $payload, User $user, AccountSetup $setup): array
    {
        if ($step === 1) {
            $payload['admin_first_name'] = $this->cleanString($payload['admin_first_name'] ?? null);
            $payload['admin_last_name'] = $this->cleanString($payload['admin_last_name'] ?? null);
            $payload['admin_role'] = $this->cleanString($payload['admin_role'] ?? null);
            $payload['full_name'] = $this->cleanString($payload['full_name'] ?? null);
            if ($payload['full_name'] === null) {
                $combinedName = trim(implode(' ', array_filter([
                    $payload['admin_first_name'] ?? null,
                    $payload['admin_last_name'] ?? null,
                ])));
                $payload['full_name'] = $combinedName !== '' ? $combinedName : null;
            }
            $payload['business_registration_number'] = $this->cleanString($payload['business_registration_number'] ?? null);
            $payload['trade_service_type'] = $this->cleanString($payload['trade_service_type'] ?? null);
            $payload['company_name'] = $this->cleanString($payload['company_name'] ?? null) ?: $user->company_name;
            $payload['contact_primary_phone'] = $this->normalizePhoneValue($payload['contact_primary_phone'] ?? null);
            $payload['contact_mobile_phone'] = $this->normalizePhoneValue($payload['contact_mobile_phone'] ?? null);
            $payload['contact_business_email'] = $this->cleanString($payload['contact_business_email'] ?? null);
            $payload['contact_full_name'] = $payload['full_name'];
            $payload['preferred_contact_method'] = $this->cleanString($payload['preferred_contact_method'] ?? null);
            $payload['emergency_contact_name'] = $this->cleanString($payload['emergency_contact_name'] ?? null);
            $payload['emergency_contact_phone'] = $this->normalizePhoneValue($payload['emergency_contact_phone'] ?? null);
            $payload['emergency_contact_relationship'] = $this->cleanString($payload['emergency_contact_relationship'] ?? null);
        }

        if ($step === 2) {
            $payload = $this->normalizeCountryPayload($payload);
            $payload['full_name'] = $this->cleanString($payload['full_name'] ?? null);
            if ($payload['full_name'] !== null) {
                $nameParts = preg_split('/\s+/', $payload['full_name']) ?: [];
                $payload['admin_first_name'] = $this->cleanString($nameParts[0] ?? null);
                $payload['admin_last_name'] = $this->cleanString(
                    count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : null
                );
            }
            $payload['admin_role'] = $this->cleanString($payload['admin_role'] ?? null);
            $payload['contact_primary_phone'] = $this->normalizePhoneValue($payload['contact_primary_phone'] ?? null);
            $payload['contact_business_email'] = $this->cleanString($payload['contact_business_email'] ?? null);
            $payload['profile_date_of_birth'] = $this->cleanString($payload['profile_date_of_birth'] ?? null);
            $payload['profile_nationality'] = $this->cleanString($payload['profile_nationality'] ?? null);
            $payload['company_address'] = $this->cleanString($payload['company_address'] ?? null);
            $payload['company_address_line_2'] = $this->cleanString($payload['company_address_line_2'] ?? null);
            $payload['company_city'] = $this->cleanString($payload['company_city'] ?? null);
            $payload['company_state'] = $this->cleanString($payload['company_state'] ?? null);
            $payload['company_zip'] = $this->cleanString($payload['company_zip'] ?? null);
            $payload['company_country'] = $this->cleanString($payload['company_country'] ?? null);
            $payload['pref_language'] = $this->cleanString($payload['pref_language'] ?? null);
            $payload['linkedin_profile'] = $this->cleanString($payload['linkedin_profile'] ?? null);
            $payload['contact_website'] = $this->cleanString($payload['contact_website'] ?? null);
            $payload['company_logo_url'] = $this->cleanString($payload['company_logo_url'] ?? null);
            $payload['company_logo_path'] = $this->cleanString($payload['company_logo_path'] ?? null);
            $payload['step_2_confirmation_ack'] = filter_var(
                $payload['step_2_confirmation_ack'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $payload['step_2_confirmation_ack'] = $payload['step_2_confirmation_ack'] ?? false;
        }

        if ($step === 3) {
            $payload = $this->normalizeCountryPayload($payload);
            $payload['company_id_number'] = $this->cleanString(
                $payload['company_id_number'] ?? null
            ) ?: $this->formatCompanyIdNumber($setup);
            foreach ([
                'company_name',
                'business_number',
                'company_status',
                'business_type',
                'currency_code',
                'company_address',
                'company_city',
                'company_state',
                'company_zip',
                'company_country',
                'business_location',
                'contact_business_email',
                'contact_alt_email',
                'contact_website',
                'fax_number',
                'linkedin_profile',
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
                'authorized_contact_email',
            ] as $field) {
                if (array_key_exists($field, $payload)) {
                    $payload[$field] = $this->cleanString($payload[$field]);
                }
            }
            $payload['business_registration_number'] = $this->cleanString(
                $payload['business_registration_number'] ?? null
            );
            $payload['business_structure'] = $this->cleanString(
                $payload['business_structure'] ?? null
            );
            $payload['tax_number'] = $this->cleanString($payload['tax_number'] ?? null);
            $payload['pst_qst_number'] = $this->cleanString($payload['pst_qst_number'] ?? null);
            $payload['incorporation_date'] = $this->cleanString(
                $payload['incorporation_date'] ?? null
            );
            $payload['company_description'] = $this->cleanString(
                $payload['company_description'] ?? null
            );
            $payload['primary_services'] = $this->cleanStringArray(
                $payload['primary_services'] ?? []
            );
            $payload['contact_primary_phone'] = $this->normalizePhoneValue($payload['contact_primary_phone'] ?? null);
            $payload['authorized_contact_phone'] = $this->normalizePhoneValue(
                $payload['authorized_contact_phone'] ?? null
            );
            $payload['business_contact_name'] = $payload['authorized_contact_name'];
            $payload['business_contact_phone'] = $payload['authorized_contact_phone'];
            $payload['business_contact_email'] = $payload['authorized_contact_email'];
            $payload['director_owner_name'] = $payload['owner_name'];
        }

        if ($step === 4) {
            $payload['professional_licenses'] = $this->normalizeProfessionalLicenses(
                $payload['professional_licenses'] ?? [],
            );
        }

        if ($step === 5) {
            $payload = $this->normalizeInsuranceCountryPayload($payload);
            foreach ([
                'insurance_company_name',
                'insurance_policy_number',
                'insurance_legal_business_name',
                'insurance_coverage_type',
                'insurance_issuing_country',
                'insurance_issuing_authority',
                'insurance_certificate_number',
                'insurance_description',
                'insurance_additional_insured',
                'insurance_certificate_path',
                'insurance_certificate_url',
                'insurance_certificate_name',
            ] as $field) {
                if (array_key_exists($field, $payload)) {
                    $payload[$field] = $this->cleanString($payload[$field]);
                }
            }
            $payload['insurance_policy_start_date'] = $this->cleanString(
                $payload['insurance_policy_start_date'] ?? null
            );
            $payload['insurance_policy_end_date'] = $this->cleanString(
                $payload['insurance_policy_end_date'] ?? null
            );
            $payload['insurance_issue_date'] = $this->cleanString(
                $payload['insurance_issue_date'] ?? null
            );
            $payload['insurance_coverage_amount'] = $this->normalizeDecimalValue(
                $payload['insurance_coverage_amount'] ?? null
            );
            $payload['insurance_deductible_amount'] = $this->normalizeDecimalValue(
                $payload['insurance_deductible_amount'] ?? null
            );
        }

        if ($step === 6) {
            $payload['subscription_plan'] = $this->cleanString(
                $payload['subscription_plan'] ?? null
            );
            $payload['billing_cycle'] = $this->cleanString(
                $payload['billing_cycle'] ?? null
            ) ?: 'annually';
        }

        if ($step === 7) {
            $payload['additional_user_licenses'] = max(
                0,
                (int) ($payload['additional_user_licenses'] ?? 0)
            );
            $payload['user_licenses'] = $this->normalizeUserLicenses(
                $payload['user_licenses'] ?? []
            );
        }

        if ($step === 8) {
            $payload['payment_schedule_start_date'] = $this->cleanString(
                $payload['payment_schedule_start_date'] ?? null
            );
            $payload['payment_schedule_ack'] = filter_var(
                $payload['payment_schedule_ack'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $payload['payment_schedule_ack'] = $payload['payment_schedule_ack'] ?? false;
            $payload['payment_schedule_acknowledged_at'] = $payload['payment_schedule_ack']
                ? now()->toIso8601String()
                : null;
            $payload['payment_schedule_items'] = $this->cleanPaymentScheduleItems(
                $payload['payment_schedule_items'] ?? []
            );
            $payload['payment_schedule_service_label'] = $this->cleanString(
                $payload['payment_schedule_service_label'] ?? null
            );
            $payload['payment_schedule_summary_note'] = $this->cleanString(
                $payload['payment_schedule_summary_note'] ?? null
            );
            $payload['payment_schedule_amount'] = $this->normalizeDecimalValue(
                $payload['payment_schedule_amount'] ?? null
            );
        }

        if ($step === 9) {
            $payload['primary_cardholder_name'] = $this->cleanString($payload['primary_cardholder_name'] ?? null);
            $payload['primary_cardholder_kind'] = 'company';
            $payload['primary_card_number'] = preg_replace('/\D/', '', (string) ($payload['primary_card_number'] ?? ''));
            $payload['primary_card_expiry'] = $this->normalizeCardExpiry($payload['primary_card_expiry'] ?? null);
            $payload['primary_card_cvv'] = preg_replace('/\D/', '', (string) ($payload['primary_card_cvv'] ?? ''));
            $payload['primary_card_last_four'] = substr($payload['primary_card_number'], -4);
            $payload['primary_card_type'] = $this->detectCardType($payload['primary_card_number']);
            $payload['backup_cardholder_name'] = $this->cleanString($payload['backup_cardholder_name'] ?? null);
            $payload['backup_cardholder_kind'] = 'person';
            $payload['backup_card_number'] = preg_replace('/\D/', '', (string) ($payload['backup_card_number'] ?? ''));
            $payload['backup_card_expiry'] = $this->normalizeCardExpiry($payload['backup_card_expiry'] ?? null);
            $payload['backup_card_cvv'] = preg_replace('/\D/', '', (string) ($payload['backup_card_cvv'] ?? ''));
            $payload['backup_card_last_four'] = $payload['backup_card_number'] !== '' ? substr($payload['backup_card_number'], -4) : null;
            $payload['backup_card_type'] = $payload['backup_card_number'] !== '' ? $this->detectCardType($payload['backup_card_number']) : null;
            $payload['payment_method_ack'] = filter_var($payload['payment_method_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $payload['payment_method_acknowledged_at'] = $payload['payment_method_ack'] ? now() : null;
        }

        if ($step === 10) {
            $payload['setup_contacts'] = $this->normalizeSetupContacts($payload['setup_contacts'] ?? []);
        }

        if ($step === 11) {
            $payload['security_username'] = $this->cleanString($payload['security_username'] ?? null);
            $payload['contact_primary_phone'] = $this->cleanString($payload['contact_primary_phone'] ?? null);
            $payload['recovery_phone'] = $this->cleanString($payload['recovery_phone'] ?? null);
            $payload['contact_business_email'] = $this->cleanString($payload['contact_business_email'] ?? null);
            $payload['recovery_email'] = $this->cleanString($payload['recovery_email'] ?? null);
            $payload['security_password_hash'] = Hash::make((string) ($payload['security_password'] ?? ''));
            $payload['security_pin_hash'] = Hash::make((string) ($payload['security_pin'] ?? ''));
            $payload['security_2fa'] = true;
            $payload['security_policy_ack'] = true;
            $payload['email_verify_status'] = $setup->email_verify_status ?: 'pending';
            $payload['security_credentials_expires_at'] = now()->addHours(48);
            $payload['mfa_email'] = $payload['contact_business_email'];
            $payload['mfa_phone'] = $payload['contact_primary_phone'];
            $payload['mfa_verified_at'] = now();
        }

        if ($step === 12) {
            $payload['declaration_ack'] = filter_var($payload['declaration_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $payload['declaration_full_name'] = $this->cleanString($payload['declaration_full_name'] ?? null);
            $payload['declaration_position_title'] = $this->cleanString($payload['declaration_position_title'] ?? null);
            $payload['declaration_signed_at'] = $this->cleanString($payload['declaration_signed_at'] ?? null);
            $payload['declaration_initials'] = strtoupper((string) $this->cleanString($payload['declaration_initials'] ?? null));
            $payload['declaration_signature'] = $this->cleanString($payload['declaration_signature'] ?? null);
        }

        if ($step === 13) {
            $payload['preview_confirm_ack'] = filter_var($payload['preview_confirm_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $payload['preview_full_name'] = $this->cleanString($payload['preview_full_name'] ?? null);
            $payload['preview_position_title'] = $this->cleanString($payload['preview_position_title'] ?? null);
            $payload['preview_initials'] = strtoupper((string) $this->cleanString($payload['preview_initials'] ?? null));
            $payload['preview_signature'] = $this->cleanString($payload['preview_signature'] ?? null);
        }

        if ($step === 14) {
            $payload['email_verify_status'] = $this->cleanString(
                $payload['email_verify_status'] ?? null
            ) ?: 'pending';
        }

        if ($step === 15) {
            // This is an activation workflow marker only. Payment processing
            // is deliberately handled by a future payment integration.
            $payload['activation_status'] = $setup->activation_status === 'active'
                ? 'active'
                : 'in_progress';
            $payload['activation_initiated_at'] = $setup->activation_initiated_at
                ?: now()->toIso8601String();
        }

        if ($step === 16) {
            $payload['safety_ack'] = filter_var(
                $payload['safety_ack'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $payload['safety_ack'] = $payload['safety_ack'] ?? false;
            $payload['safety_ack_at'] = $payload['safety_ack']
                ? now()->toIso8601String()
                : null;
            $payload['safety_policy_version'] = $this->cleanString(
                $payload['safety_policy_version'] ?? null
            ) ?: '2026-07-19';
            $payload['safety_ack_ip'] = request()->ip();
            $payload['safety_ack_user_agent'] = $this->cleanString(
                request()->userAgent()
            );
        }

        if ($step === 17) {
            $payload['terms_ack'] = filter_var(
                $payload['terms_ack'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $payload['terms_ack'] = $payload['terms_ack'] ?? false;
            $payload['privacy_ack'] = filter_var(
                $payload['privacy_ack'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $payload['privacy_ack'] = $payload['privacy_ack'] ?? false;
        }

        unset($payload['company_logo_preview']);
        if ($step !== 11) {
            unset($payload['security_password_confirm'], $payload['security_pin_confirm']);
        }

        return $payload;
    }

    protected function assertSetupCanBeCompleted(AccountSetup $setup, User $user): void
    {
        $errors = [];

        for ($i = 1; $i <= self::TOTAL_STEPS; $i += 1) {
            $key = 'step_'.$i.'_done';
            if (! (bool) $setup->{$key}) {
                $errors['steps'][] = 'Please complete all setup steps before finishing.';
                break;
            }
        }

        $requiredColumns = [
            'company_name' => 'Company name is required before completion.',
            'company_address' => 'Business address is required before completion.',
            'company_country_id' => 'Business country is required before completion.',
            'contact_business_email' => 'Business email is required before completion.',
        ];

        foreach ($requiredColumns as $field => $message) {
            if ($this->isBlank($setup->{$field} ?? null)) {
                $errors[$field][] = $message;
            }
        }

        $requiredData = [
            'full_name' => 'Full name is required before completion.',
            'profile_date_of_birth' => 'Date of birth is required before completion.',
            'profile_nationality' => 'Nationality is required before completion.',
            'step_2_confirmation_ack' => 'Step 2 confirmation is required before completion.',
        ];

        foreach ($requiredData as $field => $message) {
            if ($this->isBlank($setup->{$field} ?? null)) {
                $errors[$field][] = $message;
            }
        }

        if (! $this->professionalLicensesTableExists()) {
            $errors['professional_licenses'][] = 'Professional licenses storage is not available yet. Please run the latest database migrations.';
        } else {
            $professionalLicenses = $setup->professionalLicenses()->get();
            if ($professionalLicenses->isEmpty()) {
                $errors['professional_licenses'][] = 'At least one professional license is required before completion.';
            } else {
                foreach ($professionalLicenses as $index => $license) {
                    if (
                        $this->isBlank($license->license_name) ||
                        $this->isBlank($license->license_number) ||
                        $this->isBlank($license->legal_business_name) ||
                        $this->isBlank($license->license_type) ||
                        empty($license->issuing_country_id) ||
                        $this->isBlank($license->issuing_authority) ||
                        $license->issue_date === null ||
                        $license->expiry_date === null ||
                        $this->isBlank($license->license_status) ||
                        $this->isBlank($license->license_website)
                    ) {
                        $errors['professional_licenses'][] = 'Professional license row '.($index + 1).' is incomplete.';
                        break;
                    }
                }
            }
        }

        $requiredInsuranceFields = [
            'insurance_company_name' => 'Insurance company name is required before completion.',
            'insurance_policy_number' => 'Policy number is required before completion.',
            'insurance_legal_business_name' => 'Legal business name on policy is required before completion.',
            'insurance_coverage_type' => 'Coverage type is required before completion.',
            'insurance_policy_start_date' => 'Policy start date is required before completion.',
            'insurance_policy_end_date' => 'Policy end date is required before completion.',
            'insurance_coverage_amount' => 'Coverage amount is required before completion.',
            'insurance_issuing_country_id' => 'Issuing country is required before completion.',
            'insurance_issuing_authority' => 'Issued by (authority / insurer) is required before completion.',
            'insurance_certificate_number' => 'Insurance certificate number is required before completion.',
            'insurance_issue_date' => 'Insurance issue date is required before completion.',
            'insurance_certificate_path' => 'Insurance certificate upload is required before completion.',
        ];

        foreach ($requiredInsuranceFields as $field => $message) {
            if ($this->isBlank(data_get($setup->data, "setup_form.{$field}"))) {
                $errors[$field][] = $message;
            }
        }

        if (count((array) data_get($setup->data, 'setup_form.primary_services', [])) < 1) {
            $errors['primary_services'][] = 'At least one primary service is required before completion.';
        }

        $userLicenses = (array) data_get($setup->data, 'setup_form.user_licenses', []);
        if (count($userLicenses) < 1) {
            $errors['user_licenses'][] = 'At least one user license is required before completion.';
        } else {
            foreach ($userLicenses as $index => $license) {
                if (
                    ! is_array($license) ||
                    $this->isBlank($license['full_name'] ?? null) ||
                    $this->isBlank($license['phone_number'] ?? null) ||
                    $this->isBlank($license['license_number'] ?? null)
                ) {
                    $errors['user_licenses'][] = 'User license row '.($index + 1).' is incomplete.';
                    break;
                }
            }
        }

        if (! (bool) $setup->payment_schedule_ack) {
            $errors['payment_schedule_ack'][] = 'Please review and accept the payment schedule before completion.';
        }

        if ($setup->payment_schedule_start_date === null) {
            $errors['payment_schedule_start_date'][] = 'A payment schedule start date is required before completion.';
        }

        if ($this->isBlank($setup->subscription_plan)) {
            $errors['subscription_plan'][] = 'A service plan is required before completion.';
        }

        if ($this->setupContactsTableExists() && ! $setup->setupContacts()->exists()) {
            $errors['setup_contacts'][] = 'At least one company contact is required before completion.';
        }

        if ($this->isBlank($setup->billing_cycle)) {
            $errors['billing_cycle'][] = 'A billing cycle is required before completion.';
        }

        if ($this->isBlank($setup->billing_method)) {
            $errors['billing_method'][] = 'A payment method is required before completion.';
        }

        if ($this->isBlank($setup->billing_address)) {
            $errors['billing_address'][] = 'A billing address is required before completion.';
        }

        if (! (bool) $setup->billing_policy_ack) {
            $errors['billing_policy_ack'][] = 'Billing policy acceptance is required before completion.';
        }

        $requiredAdminColumns = [
            'admin_first_name' => 'Administrator first name is required before completion.',
            'admin_last_name' => 'Administrator last name is required before completion.',
            'admin_role' => 'Administrator role is required before completion.',
            'admin_phone' => 'Administrator phone is required before completion.',
            'admin_email' => 'Administrator email is required before completion.',
            'admin_created_date' => 'Account creation date is required before completion.',
            'recovery_email' => 'Recovery email is required before completion.',
        ];

        foreach ($requiredAdminColumns as $field => $message) {
            if ($this->isBlank($setup->{$field} ?? null)) {
                $errors[$field][] = $message;
            }
        }

        if (! (bool) $setup->safety_ack) {
            $errors['safety_ack'][] = 'Account safety acceptance is required before completion.';
        }

        if (! (bool) $setup->terms_ack) {
            $errors['terms_ack'][] = 'Terms acceptance is required before completion.';
        }

        if (! (bool) $setup->privacy_ack) {
            $errors['privacy_ack'][] = 'Privacy policy acceptance is required before completion.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function normalizeCountryPayload(array $payload): array
    {
        $country = $this->resolveCountryByIdOrName(
            $payload['company_country_id'] ?? null,
            $payload['company_country'] ?? null
        );

        if ($country['id']) {
            $payload['company_country_id'] = $country['id'];
            $payload['company_country'] = $country['name'];
        }

        return $payload;
    }

    protected function normalizeLicenseCountryPayload(array $payload): array
    {
        $country = $this->resolveCountryByIdOrName(
            $payload['contractor_license_country_id'] ?? null,
            $payload['contractor_license_country'] ?? null
        );

        if ($country['id']) {
            $payload['contractor_license_country_id'] = $country['id'];
            $payload['contractor_license_country'] = $country['name'];
        }

        return $payload;
    }

    protected function normalizeInsuranceCountryPayload(array $payload): array
    {
        $country = $this->resolveCountryByIdOrName(
            $payload['insurance_issuing_country_id'] ?? null,
            $payload['insurance_issuing_country'] ?? null
        );

        if ($country['id']) {
            $payload['insurance_issuing_country_id'] = $country['id'];
            $payload['insurance_issuing_country'] = $country['name'];
        }

        return $payload;
    }

    protected function normalizeBillingCountryPayload(array $payload): array
    {
        $country = $this->resolveCountryByIdOrName(
            $payload['billing_country_id'] ?? null,
            $payload['billing_country'] ?? null
        );

        if ($country['id']) {
            $payload['billing_country_id'] = $country['id'];
            $payload['billing_country'] = $country['name'];
        }

        return $payload;
    }

    protected function resolveCountryByIdOrName(mixed $countryId, mixed $countryName): array
    {
        if (! empty($countryId)) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->find($countryId);
            if ($country) {
                return ['id' => (int) $country->id, 'name' => $country->country_name];
            }
        }

        $normalizedName = $this->cleanString($countryName);
        if ($normalizedName !== null) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->where('country_name', $normalizedName)
                ->first();
            if ($country) {
                return ['id' => (int) $country->id, 'name' => $country->country_name];
            }
        }

        return ['id' => null, 'name' => null];
    }

    protected function serializeSetup(AccountSetup $setup, ?User $user = null): array
    {
        $setup->makeHidden(['data']);
        $payload = $setup->toArray();
        $setupForm = data_get($setup->data, 'setup_form', []);
        if (is_array($setupForm)) {
            $payload = array_replace($payload, $setupForm);
        }
        $payload['professional_licenses'] = $this->professionalLicensesTableExists()
            ? $setup->professionalLicenses()
                ->get()
                ->map(fn (AccountSetupProfessionalLicense $license) => [
                    'license_name' => $license->license_name,
                    'license_number' => $license->license_number,
                    'legal_business_name' => $license->legal_business_name,
                    'license_type' => $license->license_type,
                    'issuing_country_id' => $license->issuing_country_id,
                    'issuing_country' => $license->issuing_country,
                    'issuing_authority' => $license->issuing_authority,
                    'issue_date' => optional($license->issue_date)->toDateString(),
                    'expiry_date' => optional($license->expiry_date)->toDateString(),
                    'license_status' => $license->license_status,
                    'license_website' => $license->license_website,
                    'verification_url' => $license->verification_url,
                    'description_scope' => $license->description_scope,
                ])
                ->all()
            : [];
        $payload['setup_contacts'] = $this->setupContactsTableExists()
            ? $setup->setupContacts()
                ->get()
                ->map(fn (AccountSetupContact $contact) => [
                    'department_name' => $contact->department_name,
                    'contact_person' => $contact->contact_person,
                    'phone_number' => $contact->phone_number,
                    'email' => $contact->email,
                    'position_title' => $contact->position_title,
                ])
                ->all()
            : [];
        $payload['company_id_number'] = $payload['company_id_number'] ?? $this->formatCompanyIdNumber($setup);
        $payload['current_step'] = max(1, min(self::TOTAL_STEPS, (int) ($setup->current_step ?: 1)));
        $payload['expiry_date'] = $payload['expiry_date'] ?? optional($user)->expiry_date?->toDateString();

        return $payload;
    }

    protected function formatCompanyIdNumber(AccountSetup $setup): string
    {
        $referenceId = $setup->id ?: $setup->user_id ?: 0;

        return sprintf('CNT-%s-%06d', now()->format('Y'), $referenceId);
    }

    protected function extractUserColumns(int $step, array $payload): array
    {
        return [];
    }

    protected function normalizeProfessionalLicenses(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $country = $this->resolveCountryByIdOrName(
                $row['issuing_country_id'] ?? null,
                $row['issuing_country'] ?? null,
            );

            $licenseName = $this->cleanString($row['license_name'] ?? null);
            $licenseNumber = $this->cleanString($row['license_number'] ?? null);
            $legalBusinessName = $this->cleanString($row['legal_business_name'] ?? null);
            $licenseType = $this->cleanString($row['license_type'] ?? null);
            $issuingAuthority = $this->cleanString($row['issuing_authority'] ?? null);
            $issueDate = $this->cleanString($row['issue_date'] ?? null);
            $expiryDate = $this->cleanString($row['expiry_date'] ?? null);
            $licenseStatus = $this->cleanString($row['license_status'] ?? null);
            $licenseWebsite = $this->cleanString($row['license_website'] ?? null);
            $verificationUrl = $this->cleanString($row['verification_url'] ?? null);
            $descriptionScope = $this->cleanString($row['description_scope'] ?? null);

            if (
                $licenseName === null &&
                $licenseNumber === null &&
                $legalBusinessName === null &&
                $licenseType === null &&
                $country['id'] === null &&
                $issuingAuthority === null &&
                $issueDate === null &&
                $expiryDate === null &&
                $licenseStatus === null &&
                $licenseWebsite === null &&
                $verificationUrl === null &&
                $descriptionScope === null
            ) {
                continue;
            }

            $items[] = [
                'license_name' => $licenseName,
                'license_number' => $licenseNumber,
                'legal_business_name' => $legalBusinessName,
                'license_type' => $licenseType,
                'issuing_country_id' => $country['id'],
                'issuing_country' => $country['name'],
                'issuing_authority' => $issuingAuthority,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'license_status' => $licenseStatus,
                'license_website' => $licenseWebsite,
                'verification_url' => $verificationUrl,
                'description_scope' => $descriptionScope,
            ];
        }

        return $items;
    }

    protected function syncProfessionalLicenses(AccountSetup $setup, array $licenses): void
    {
        if (! $this->professionalLicensesTableExists()) {
            return;
        }

        $setup->professionalLicenses()->delete();

        foreach ($licenses as $index => $license) {
            if (! is_array($license)) {
                continue;
            }

            $setup->professionalLicenses()->create([
                'license_name' => $license['license_name'] ?? null,
                'license_number' => $license['license_number'] ?? null,
                'legal_business_name' => $license['legal_business_name'] ?? null,
                'license_type' => $license['license_type'] ?? null,
                'issuing_country_id' => $license['issuing_country_id'] ?? null,
                'issuing_country' => $license['issuing_country'] ?? null,
                'issuing_authority' => $license['issuing_authority'] ?? null,
                'issue_date' => $license['issue_date'] ?? null,
                'expiry_date' => $license['expiry_date'] ?? null,
                'license_status' => $license['license_status'] ?? null,
                'license_website' => $license['license_website'] ?? null,
                'verification_url' => $license['verification_url'] ?? null,
                'description_scope' => $license['description_scope'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    protected function normalizeSetupContacts(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => [
                'department_name' => $this->cleanString($row['department_name'] ?? null),
                'contact_person' => $this->cleanString($row['contact_person'] ?? null),
                'phone_number' => $this->cleanString($row['phone_number'] ?? null),
                'email' => $this->cleanString($row['email'] ?? null),
                'position_title' => $this->cleanString($row['position_title'] ?? null),
            ])
            ->values()
            ->all();
    }

    protected function syncSetupContacts(AccountSetup $setup, array $contacts): void
    {
        if (! $this->setupContactsTableExists()) {
            return;
        }

        $setup->setupContacts()->delete();
        $setup->setupContacts()->createMany(
            collect($contacts)
                ->values()
                ->map(fn (array $contact, int $index) => [
                    ...$contact,
                    'sort_order' => $index,
                ])
                ->all()
        );
    }

    protected function professionalLicensesTableExists(): bool
    {
        return Schema::hasTable('account_setup_professional_licenses');
    }

    protected function setupContactsTableExists(): bool
    {
        return Schema::hasTable('account_setup_contacts');
    }

    protected function syncJsonBackup(
        AccountSetup $setup,
        int $step,
        array $stepPayload,
        array $columnPayload = []
    ): void {
        $data = $setup->data;
        if (! is_array($data)) {
            $data = [];
        }

        $setupForm = $data['setup_form'] ?? [];
        if (! is_array($setupForm)) {
            $setupForm = [];
        }
        $setupForm = array_replace($setupForm, $stepPayload);

        $setupColumns = $data['setup_columns'] ?? [];
        if (! is_array($setupColumns)) {
            $setupColumns = [];
        }
        if (! empty($columnPayload)) {
            $setupColumns = array_replace($setupColumns, $columnPayload);
        }

        $snapshots = $data['step_snapshots'] ?? [];
        if (! is_array($snapshots)) {
            $snapshots = [];
        }
        $snapshots[(string) $step] = [
            'saved_at' => now()->toIso8601String(),
            'payload' => $stepPayload,
            'columns' => $columnPayload,
        ];

        $data['setup_form'] = $setupForm;
        $data['setup_columns'] = $setupColumns;
        $data['step_snapshots'] = $snapshots;
        $data['last_saved_step'] = $step;

        $setup->data = $data;
    }

    protected function normalizePhoneValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $trimmed);
        if (! is_string($normalized) || $normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = '+'.substr($normalized, 2);
        }

        if (str_starts_with($normalized, '+')) {
            $normalized = '+'.preg_replace('/\D/', '', substr($normalized, 1));
        } else {
            $normalized = '+'.preg_replace('/\D/', '', $normalized);
        }

        return $normalized === '+' ? null : $normalized;
    }

    protected function cleanStringArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            $clean = $this->cleanString($item);
            if ($clean !== null && ! in_array($clean, $items, true)) {
                $items[] = $clean;
            }
        }

        return $items;
    }

    protected function cleanOperatingHours(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $start = $this->cleanString($row['start'] ?? null);
            $end = $this->cleanString($row['end'] ?? null);
            $days = $this->cleanString($row['days'] ?? null);

            if ($start === null && $end === null && $days === null) {
                continue;
            }

            $items[] = [
                'start' => $start,
                'end' => $end,
                'days' => $days,
            ];
        }

        return $items;
    }

    protected function cleanAdditionalInsurances(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $type = $this->cleanString($row['type'] ?? null);
            $provider = $this->cleanString($row['provider'] ?? null);
            $policyNumber = $this->cleanString($row['policy_number'] ?? null);
            $expiryDate = $this->cleanString($row['expiry_date'] ?? null);

            if ($type === null && $provider === null && $policyNumber === null && $expiryDate === null) {
                continue;
            }

            $items[] = [
                'type' => $type,
                'provider' => $provider,
                'policy_number' => $policyNumber,
                'expiry_date' => $expiryDate,
            ];
        }

        return $items;
    }

    protected function normalizeUserLicenses(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $fullName = $this->cleanString($row['full_name'] ?? null);
            $phoneNumber = $this->cleanString($row['phone_number'] ?? null);
            $licenseNumber = $this->cleanString($row['license_number'] ?? null)
                ?: sprintf('UL-260718-%04d', $index + 1);
            $expiryNote = $this->cleanString($row['expiry_note'] ?? null)
                ?: 'Announced after payment';

            if ($fullName === null && $phoneNumber === null && $licenseNumber === null) {
                continue;
            }

            $items[] = [
                'full_name' => $fullName,
                'phone_number' => $phoneNumber,
                'license_number' => $licenseNumber,
                'expiry_note' => $expiryNote,
            ];
        }

        return $items;
    }

    protected function cleanTeamMembers(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $role = $this->cleanString($row['role'] ?? null);
            $accessLevel = $this->cleanString($row['access_level'] ?? null);
            $fullName = $this->cleanString($row['full_name'] ?? null);
            $email = $this->cleanString($row['email'] ?? null);
            $phone = $this->cleanString($row['phone'] ?? null);
            $department = $this->cleanString($row['department'] ?? null);

            if (
                $role === null &&
                $accessLevel === null &&
                $fullName === null &&
                $email === null &&
                $phone === null &&
                $department === null
            ) {
                continue;
            }

            $items[] = [
                'role' => $role,
                'access_level' => $accessLevel,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'department' => $department,
            ];
        }

        return $items;
    }

    /** Determine the display brand from a card number without retaining the PAN. */
    protected function detectCardType(string $cardNumber): string
    {
        if (preg_match('/^4/', $cardNumber)) {
            return 'VISA';
        }

        if (preg_match('/^(5[1-5]|2[2-7])/', $cardNumber)) {
            return 'Mastercard';
        }

        if (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        }

        return 'Other';
    }

    /**
     * Accept the UI's MM/YY or MM/YYYY card-expiry format and store MM / YYYY.
     */
    protected function normalizeCardExpiry(mixed $value): ?string
    {
        $value = $this->cleanString($value);

        if ($value === null) {
            return null;
        }

        if (! preg_match('/^(\d{2})\s*\/\s*(\d{2}|\d{4})$/', $value, $matches)) {
            return $value;
        }

        $month = (int) $matches[1];
        if ($month < 1 || $month > 12) {
            return $value;
        }

        $year = $matches[2];
        if (strlen($year) === 2) {
            $year = '20'.$year;
        }

        return sprintf('%02d / %s', $month, $year);
    }

    protected function cleanPaymentScheduleItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $paymentNumber = (int) ($row['payment_number'] ?? 0);
            $servicePeriodStart = $this->cleanString($row['service_period_start'] ?? null);
            $servicePeriodEnd = $this->cleanString($row['service_period_end'] ?? null);
            $autoChargeDate = $this->cleanString($row['auto_charge_date'] ?? null);
            $chargingDate = $this->cleanString($row['charging_date'] ?? null);
            $amount = $this->normalizeDecimalValue($row['amount'] ?? null);

            if (
                $paymentNumber < 1 &&
                $servicePeriodStart === null &&
                $servicePeriodEnd === null &&
                $autoChargeDate === null &&
                $chargingDate === null &&
                $amount === null
            ) {
                continue;
            }

            $items[] = [
                'payment_number' => max(1, $paymentNumber),
                'service_period_start' => $servicePeriodStart,
                'service_period_end' => $servicePeriodEnd,
                'auto_charge_date' => $autoChargeDate,
                'charging_date' => $chargingDate,
                'amount' => $amount,
            ];
        }

        return $items;
    }

    protected function cleanSecurityQuestions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $question = $this->cleanString($row['question'] ?? null);
            $answer = $this->cleanString($row['answer'] ?? null);

            if ($question === null && $answer === null) {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }

    protected function normalizeDecimalValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[^\d.]/', '', (string) $value);
        if (! is_string($clean) || $clean === '') {
            return null;
        }

        return $clean;
    }

    protected function cleanString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    protected function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        return false;
    }
}
