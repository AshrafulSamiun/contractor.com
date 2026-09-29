<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\BillingInvoice;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    protected function setup(Request $request): AccountSetup
    {
        $projectId = (int) $request->user()->project_id;
        abort_if($projectId < 1, 404, 'No account setup is assigned to this project.');

        return AccountSetup::query()->findOrFail($projectId);
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
                'system_admin' => $setup->data['system_admin'] ?? [],
                'account_info' => [
                    'account_number' => 'ACC-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                    'created_at' => $user->created_at,
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'position' => $user->role ? ucfirst(str_replace('_', ' ', $user->role)) : 'User',
                ],
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
            'position' => ['nullable', 'string', 'max:100'],
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
            'system_admin' => array_merge($setup->data['system_admin'] ?? [], [
                'position' => $request->input('position'),
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
        $form = data_get($setup->data, 'setup_form', []);
        $schedule = is_array($form['payment_schedule_items'] ?? null)
            ? collect($form['payment_schedule_items'])->sortBy('auto_charge_date')->values()
            : collect();
        $today = now()->startOfDay();
        $upcoming = $schedule->filter(fn ($item) => ! empty($item['auto_charge_date']) && Carbon::parse($item['auto_charge_date'])->startOfDay()->gte($today))->values();
        $next = $upcoming->first();

        return response()->json([
            'success' => true,
            'data' => [
                'account_setup_id' => $setup->id,
                'company_name' => $setup->company_name,
                'currency' => $setup->currency_code ?: 'CAD',
                'billing_cycle' => $setup->billing_cycle,
                'subscription_plan' => $setup->subscription_plan,
                'next_payment_date' => $next['auto_charge_date'] ?? null,
                'next_payment_amount' => $next['amount'] ?? ($form['payment_schedule_amount'] ?? null),
                'outstanding_balance' => null,
                'tax_rate' => 5,
                'card_type' => $setup->primary_card_type,
                'card_last_four' => $setup->primary_card_last_four,
                'card_expiry' => $setup->primary_card_expiry,
                'card_status' => $setup->primary_card_last_four ? 'Default' : 'Not configured',
                'payment_schedule' => $schedule->all(),
                'updated_at' => optional($setup->updated_at)->toDateString(),
            ],
        ]);
    }

    public function updateBilling(Request $request)
    {
        $setup = $this->setup($request);
        $validated = $request->validate([
            'primary_card_type' => ['nullable', 'string', 'max:50'],
            'primary_card_last_four' => ['nullable', 'regex:/^\d{4}$/'],
            'primary_card_expiry' => ['nullable', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'primary_cardholder_name' => ['nullable', 'string', 'max:150'],
        ]);
        $setup->fill($validated);
        $setup->save();

        return response()->json([
            'success' => true,
            'data' => $validated,
        ]);
    }

    /** Paid subscription invoices, with values returned in major currency units. */
    public function taxReport(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'in:paid,all'],
        ]);

        $setup = $this->setup($request);
        $from = isset($validated['from_date']) ? Carbon::parse($validated['from_date'])->startOfDay() : now()->startOfMonth();
        $to = isset($validated['to_date']) ? Carbon::parse($validated['to_date'])->endOfDay() : now()->endOfMonth();
        $taxRate = 5.0;

        $invoices = BillingInvoice::query()
            ->where('user_id', $request->user()->id)
            ->when(($validated['status'] ?? 'paid') === 'paid', fn ($query) => $query->where('status', 'paid'))
            ->orderBy('period_start')
            ->get()
            ->filter(function (BillingInvoice $invoice) use ($from, $to) {
                $date = $invoice->period_start ?: $invoice->created_at;
                return $date && $date->betweenIncluded($from, $to);
            })
            ->values()
            ->map(function (BillingInvoice $invoice) use ($setup, $taxRate) {
                $paidCents = (int) ($invoice->amount_paid ?? 0);
                $taxCents = $invoice->tax_amount !== null
                    ? (int) $invoice->tax_amount
                    : (int) round($paidCents * $taxRate / (100 + $taxRate));
                $subtotalCents = max(0, $paidCents - $taxCents);

                return [
                    'date' => optional($invoice->period_start ?: $invoice->created_at)->toDateString(),
                    'invoice_number' => $invoice->stripe_invoice_id,
                    'details' => $invoice->plan_name ?: ($setup->subscription_plan ? ucfirst($setup->subscription_plan) . ' Service Plan' : 'Software Service Plan'),
                    'subtotal' => round($subtotalCents / 100, 2),
                    'tax_name' => 'GST',
                    'tax_rate' => $taxRate,
                    'tax' => round($taxCents / 100, 2),
                    'total_payment' => round($paidCents / 100, 2),
                    'status' => ucfirst($invoice->status ?: 'paid'),
                ];
            });

        return response()->json(['success' => true, 'data' => [
            'company_name' => $setup->company_name,
            'account_number' => 'AC-' . str_pad((string) $setup->id, 6, '0', STR_PAD_LEFT),
            'country' => $setup->company_country ?: 'Not set',
            'currency' => $setup->currency_code ?: 'CAD',
            'tax_number' => $setup->tax_number ?: 'Not set',
            'tax_name' => 'GST', 'tax_rate' => $taxRate,
            'from_date' => $from->toDateString(), 'to_date' => $to->toDateString(),
            'rows' => $invoices->all(),
            'totals' => ['subtotal' => round($invoices->sum('subtotal'), 2), 'tax' => round($invoices->sum('tax'), 2), 'total_payment' => round($invoices->sum('total_payment'), 2)],
        ]]);
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
