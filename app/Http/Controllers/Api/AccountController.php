<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\BillingInvoice;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    protected function setup(Request $request): AccountSetup
    {
        return AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );
    }

    protected function mergeSetup(AccountSetup $setup, array $payload): AccountSetup
    {
        $merged = array_merge($setup->data ?? [], $payload);
        $setup->data = $merged;
        $setup->save();
        return $setup;
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $setup = $this->setup($request);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $setup->data['profile'] ?? [],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->merge([
            'phone' => $this->normalizePhoneValue($request->input('phone')),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);
        $validated = $this->normalizeCountryPayload($validated, 'country_id', 'country');

        $user->update($validated);

        $setup = $this->setup($request);
        $this->mergeSetup($setup, [
            'profile' => array_merge($setup->data['profile'] ?? [], [
                'preferred_language' => $request->input('preferred_language'),
                'timezone' => $request->input('timezone'),
                'address_line1' => $request->input('address_line1'),
                'address_line2' => $request->input('address_line2'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $setup = $this->setup($request);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'selected_plan' => $user->selected_plan,
                'stripe_subscription_status' => $user->stripe_subscription_status,
                'account_setup_completed_at' => $user->account_setup_completed_at,
                'created_at' => $user->created_at,
                'billing' => $setup->data['billing'] ?? [],
                'subscription' => $setup->data['subscription'] ?? [],
            ],
        ]);
    }

    public function billing(Request $request)
    {
        $setup = $this->setup($request);

        return response()->json([
            'success' => true,
            'data' => $setup->data['billing'] ?? [],
        ]);
    }

    public function updateBilling(Request $request)
    {
        $validated = $request->validate([
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_zip' => ['nullable', 'string', 'max:30'],
            'billing_country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'billing_country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'invoice_email' => ['nullable', 'email'],
            'billing_cycle' => ['nullable', 'in:monthly,annual'],
            'auto_renew' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'last4' => ['nullable', 'string', 'max:8'],
        ]);
        $validated = $this->normalizeCountryPayload($validated, 'billing_country_id', 'billing_country');

        $setup = $this->setup($request);
        $this->mergeSetup($setup, [
            'billing' => array_merge($setup->data['billing'] ?? [], $validated),
        ]);

        return response()->json([
            'success' => true,
            'data' => $setup->data['billing'] ?? [],
        ]);
    }

    public function security(Request $request)
    {
        $setup = $this->setup($request);

        return response()->json([
            'success' => true,
            'data' => $setup->data['security'] ?? [],
        ]);
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'security_pin' => ['nullable', 'string', 'max:10'],
            'mfa_enabled' => ['nullable', 'boolean'],
            'login_alerts' => ['nullable', 'boolean'],
            'session_timeout' => ['nullable', 'integer', 'min:5', 'max:240'],
            'device_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'current_password' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = $request->user();
        if (!empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect.',
                ], 422);
            }
            $user->password = $validated['new_password'];
            $user->save();
        }

        $payload = $validated;
        unset($payload['current_password'], $payload['new_password']);

        $setup = $this->setup($request);
        $this->mergeSetup($setup, [
            'security' => array_merge($setup->data['security'] ?? [], $payload),
        ]);

        return response()->json([
            'success' => true,
            'data' => $setup->data['security'] ?? [],
        ]);
    }

    public function recovery(Request $request)
    {
        $setup = $this->setup($request);

        return response()->json([
            'success' => true,
            'data' => $setup->data['recovery'] ?? [],
        ]);
    }

    public function updateRecovery(Request $request)
    {
        $request->merge([
            'recovery_phone' => $this->normalizePhoneValue($request->input('recovery_phone')),
            'backup_contact_phone' => $this->normalizePhoneValue($request->input('backup_contact_phone')),
        ]);
        $validated = $request->validate([
            'recovery_email' => ['nullable', 'email'],
            'recovery_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'backup_contact_name' => ['nullable', 'string', 'max:255'],
            'backup_contact_email' => ['nullable', 'email'],
            'backup_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'escalation_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'recovery_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'backup_contact_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);

        $setup = $this->setup($request);
        $this->mergeSetup($setup, [
            'recovery' => array_merge($setup->data['recovery'] ?? [], $validated),
        ]);

        return response()->json([
            'success' => true,
            'data' => $setup->data['recovery'] ?? [],
        ]);
    }

    public function invoices(Request $request)
    {
        $invoices = BillingInvoice::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    protected function normalizeCountryPayload(array $payload, string $idField, string $nameField): array
    {
        $country = null;

        if (!empty($payload[$idField])) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->find($payload[$idField]);
        } elseif (!empty($payload[$nameField])) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->where('country_name', $payload[$nameField])
                ->first();
        }

        if ($country) {
            $payload[$idField] = $country->id;
            $payload[$nameField] = $country->country_name;
            return $payload;
        }

        if (empty($payload[$idField]) && empty($payload[$nameField])) {
            $payload[$idField] = null;
            $payload[$nameField] = null;
        }

        return $payload;
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
}
