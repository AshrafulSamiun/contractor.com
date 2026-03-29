<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountSetupController extends Controller
{
    private const TOTAL_STEPS = 7;

    protected array $stepFieldMap = [
        1 => ['company_name'],
        2 => ['company_address', 'company_city', 'company_state', 'company_zip', 'company_country_id', 'company_country'],
        3 => ['company_logo_url', 'company_logo_path', 'contact_website'],
        4 => ['contact_mobile_phone', 'contact_business_email'],
        5 => ['subscription_plan'],
        6 => ['security_username', 'security_password_hash'],
        7 => ['terms_ack', 'privacy_ack', 'final_ack', 'completed_at'],
    ];

    protected array $dataFieldMap = [
        1 => ['full_name', 'business_registration_number', 'trade_service_type'],
        3 => ['registration_country', 'registration_province', 'registration_notes'],
        4 => ['contact_full_name'],
    ];

    public function show(Request $request)
    {
        $user = $request->user();
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 1]
        );

        if ($this->isBlank($setup->company_name) && !$this->isBlank($user->company_name)) {
            $setup->company_name = $user->company_name;
            $setup->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'step' => ['required', 'integer', 'min:1', 'max:' . self::TOTAL_STEPS],
            'data' => ['nullable', 'array'],
        ]);

        $step = (int) $validated['step'];
        $payload = $validated['data'] ?? [];
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );

        $payload = $this->normalizePayload($step, $payload, $request->user());
        $this->validateStepPayload($step, $payload, $request->user());

        $columnFields = $this->stepFieldMap[$step] ?? [];
        $dataFields = $this->dataFieldMap[$step] ?? [];
        $columns = array_intersect_key($payload, array_flip($columnFields));
        $extraData = array_intersect_key($payload, array_flip($dataFields));

        if ($step === 6 && !empty($payload['security_password'])) {
            $columns['security_password_hash'] = Hash::make($payload['security_password']);
        }

        $stepDone = 'step_' . $step . '_done';
        $jsonPayload = array_merge($columns, $extraData);

        DB::transaction(function () use ($setup, $columns, $jsonPayload, $step, $stepDone) {
            if (!empty($columns)) {
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
        });

        if (!empty($columns['subscription_plan'])) {
            $request->user()->forceFill([
                'selected_plan' => $columns['subscription_plan'],
            ])->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup->fresh()),
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

    public function complete(Request $request)
    {
        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );

        $this->assertSetupCanBeCompleted($setup);

        $completedAt = now();
        $setup->current_step = self::TOTAL_STEPS;
        $setup->completed_at = $completedAt;
        $setup->final_ack = true;

        for ($i = 1; $i <= self::TOTAL_STEPS; $i += 1) {
            $stepDone = 'step_' . $i . '_done';
            $setup->{$stepDone} = true;
        }

        $this->syncJsonBackup($setup, self::TOTAL_STEPS, [
            'final_ack' => true,
            'completed_at' => $completedAt->toIso8601String(),
        ], [
            'current_step' => self::TOTAL_STEPS,
            'final_ack' => true,
            'completed_at' => $completedAt->toIso8601String(),
            'step_7_done' => true,
        ]);
        $setup->save();

        if ($setup->subscription_plan) {
            $request->user()->forceFill([
                'selected_plan' => $setup->subscription_plan,
                'account_setup_completed_at' => $completedAt,
            ])->save();
        } else {
            $request->user()->forceFill([
                'account_setup_completed_at' => $completedAt,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeSetup($setup->fresh()),
        ]);
    }

    protected function validateStepPayload(int $step, array $payload, User $user): void
    {
        $rules = match ($step) {
            1 => [
                'full_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'business_registration_number' => ['required', 'string', 'max:120'],
                'trade_service_type' => ['required', 'string', 'max:120'],
            ],
            2 => [
                'company_address' => ['required', 'string', 'max:255'],
                'company_city' => ['required', 'string', 'max:120'],
                'company_state' => ['required', 'string', 'max:120'],
                'company_zip' => ['required', 'string', 'max:30'],
                'company_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
                'company_country' => ['nullable', 'string', 'max:120'],
            ],
            3 => [
                'registration_country' => ['required', 'integer', Rule::exists('countries', 'id')],
                'registration_province' => ['required', 'string'],

            ],
            4 => [
                'contact_mobile_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'contact_business_email' => ['required', 'email', 'max:255'],
            ],
            5 => [
                'subscription_plan' => ['required', Rule::in(['starter', 'growth', 'enterprise'])],
            ],
            6 => [
                'security_username' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('users', 'username')->ignore($user->id),
                ],
                'security_password' => ['required', 'string', 'min:8'],
                'security_password_confirm' => ['required', 'same:security_password'],
            ],
            7 => [
                'terms_ack' => ['accepted'],
                'privacy_ack' => ['accepted'],
                'final_ack' => ['accepted'],
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

    protected function normalizePayload(int $step, array $payload, User $user): array
    {
        if ($step === 1) {
            $payload['full_name'] = $this->cleanString($payload['full_name'] ?? null);
            $payload['business_registration_number'] = $this->cleanString($payload['business_registration_number'] ?? null);
            $payload['trade_service_type'] = $this->cleanString($payload['trade_service_type'] ?? null);
            $payload['company_name'] = $this->cleanString($payload['company_name'] ?? null) ?: $user->company_name;
        }

        if ($step === 2) {
            $payload = $this->normalizeCountryPayload($payload);
            foreach (['company_address', 'company_city', 'company_state', 'company_zip', 'company_country'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $payload[$field] = $this->cleanString($payload[$field]);
                }
            }
        }

        if ($step === 3) {
            $payload['registration_country'] = $this->cleanString($payload['registration_country'] ?? null);
            $payload['registration_province'] = $this->cleanString($payload['registration_province'] ?? null);
        }

        if ($step === 4) {
            $payload['contact_mobile_phone'] = $this->normalizePhoneValue($payload['contact_mobile_phone'] ?? null);
            $payload['contact_business_email'] = $this->cleanString($payload['contact_business_email'] ?? null);
        }

        if ($step === 5) {
            $payload['subscription_plan'] = $this->cleanString($payload['subscription_plan'] ?? null);
        }

        if ($step === 6) {
            $payload['security_username'] = $this->cleanString($payload['security_username'] ?? null);
        }

        if ($step === 7) {
            $payload['terms_ack'] = filter_var($payload['terms_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $payload['privacy_ack'] = filter_var($payload['privacy_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $payload['final_ack'] = filter_var($payload['final_ack'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        unset($payload['company_logo_preview'], $payload['security_password_confirm']);

        return $payload;
    }

    protected function assertSetupCanBeCompleted(AccountSetup $setup): void
    {
        $errors = [];

        for ($i = 1; $i <= self::TOTAL_STEPS; $i += 1) {
            $key = 'step_' . $i . '_done';
            if (!(bool) $setup->{$key}) {
                $errors['steps'][] = 'Please complete all setup steps before finishing.';
                break;
            }
        }

        $requiredColumns = [
            'company_name' => 'Company name is required before completion.',
            'company_address' => 'Business address is required before completion.',
            'company_country_id' => 'Business country is required before completion.',
            'contact_business_email' => 'Business email is required before completion.',
            'subscription_plan' => 'Service plan is required before completion.',
            'security_username' => 'Username is required before completion.',
            'security_password_hash' => 'Password is required before completion.',
        ];

        foreach ($requiredColumns as $field => $message) {
            if ($this->isBlank($setup->{$field} ?? null)) {
                $errors[$field][] = $message;
            }
        }

        $requiredData = [
            'full_name' => 'Full name is required before completion.',
            'business_registration_number' => 'Business registration number is required before completion.',
            'trade_service_type' => 'Trade / service type is required before completion.',
            'registration_country' => 'Registration Country is required before completion.',
            'registration_province' => 'Registration Province is required before completion.',
        ];

        foreach ($requiredData as $field => $message) {
            if ($this->isBlank($setup->{$field} ?? null)) {
                $errors[$field][] = $message;
            }
        }

        if (!(bool) $setup->terms_ack) {
            $errors['terms_ack'][] = 'Terms and Conditions must be accepted before completion.';
        }

        if (!(bool) $setup->privacy_ack) {
            $errors['privacy_ack'][] = 'Privacy Policy must be accepted before completion.';
        }

        if (!(bool) $setup->final_ack) {
            $errors['final_ack'][] = 'Final confirmation is required before completion.';
        }

        if (!empty($errors)) {
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

    protected function resolveCountryByIdOrName(mixed $countryId, mixed $countryName): array
    {
        if (!empty($countryId)) {
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

    protected function serializeSetup(AccountSetup $setup): array
    {
        $setup->makeHidden(['data']);
        $payload = $setup->toArray();
        $payload['current_step'] = max(1, min(self::TOTAL_STEPS, (int) ($setup->current_step ?: 1)));

        return $payload;
    }

    protected function syncJsonBackup(
        AccountSetup $setup,
        int $step,
        array $stepPayload,
        array $columnPayload = []
    ): void {
        $data = $setup->data;
        if (!is_array($data)) {
            $data = [];
        }

        $setupForm = $data['setup_form'] ?? [];
        if (!is_array($setupForm)) {
            $setupForm = [];
        }
        $setupForm = array_replace($setupForm, $stepPayload);

        $setupColumns = $data['setup_columns'] ?? [];
        if (!is_array($setupColumns)) {
            $setupColumns = [];
        }
        if (!empty($columnPayload)) {
            $setupColumns = array_replace($setupColumns, $columnPayload);
        }

        $snapshots = $data['step_snapshots'] ?? [];
        if (!is_array($snapshots)) {
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
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $trimmed);
        if (!is_string($normalized) || $normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = '+' . substr($normalized, 2);
        }

        if (str_starts_with($normalized, '+')) {
            $normalized = '+' . preg_replace('/\D/', '', substr($normalized, 1));
        } else {
            $normalized = '+' . preg_replace('/\D/', '', $normalized);
        }

        return $normalized === '+' ? null : $normalized;
    }

    protected function cleanString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
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
