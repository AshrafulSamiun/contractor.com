<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountHolder;
use App\Models\AccountHolderSuffix;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountHolderController extends Controller
{
    private const DEFAULT_ACCOUNT_TYPE_PREFIXES = [
        1 => 'C',
        2 => 'S',
        3 => 'SP',
        4 => 'E',
        5 => 'B',
        6 => 'CC',
        7 => 'G',
        8 => 'T',
        9 => 'SH',
    ];

    private const ACCOUNT_TYPE_LABELS = [
        1 => 'Customer',
        2 => 'Seller',
        3 => 'Service Provider',
        4 => 'Employee',
        5 => 'Bank',
        6 => 'Credit Card',
        7 => 'Government',
        8 => 'Tax Office',
        9 => 'Shareholder',
    ];

    private const CONTACT_METHOD_LABELS = [
        1 => 'Phone',
        2 => 'Email',
        3 => 'Other',
    ];

    private function decodeJsonField($value)
    {
        if (!$value) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeAccountHolder(AccountHolder $accountHolder, array $countryArr = [])
    {
        $accountHolder->account_type_label = self::ACCOUNT_TYPE_LABELS[(int) $accountHolder->account_type] ?? null;
        $accountHolder->prefer_contact_method_label = self::CONTACT_METHOD_LABELS[(int) $accountHolder->prefer_contact_method] ?? null;
        $accountHolder->country_name = $countryArr[$accountHolder->country] ?? null;

        return $accountHolder;
    }

    private function resolveAccountTypePrefix($accountType): string
    {
        $accountType = (int) $accountType;
        $primaryType = self::ACCOUNT_TYPE_LABELS[$accountType] ?? null;

        if (!$primaryType) {
            return 'AH';
        }

        $suffixRecord = AccountHolderSuffix::query()
            ->where('status_active', 1)
            ->where(function ($query) use ($primaryType) {
                $query->where('suffix', $primaryType)
                    ->orWhere('prifix', $primaryType);
            })
            ->first();

        if ($suffixRecord) {
            $candidate = strtoupper(trim((string) ($suffixRecord->prifix ?: $suffixRecord->suffix)));
            if ($candidate !== '') {
                return preg_replace('/[^A-Z0-9]/', '', $candidate) ?: 'AH';
            }
        }

        return self::DEFAULT_ACCOUNT_TYPE_PREFIXES[$accountType] ?? 'AH';
    }

    private function generateSystemIdentity(int $projectId, $accountType): array
    {
        $prefix = $this->resolveAccountTypePrefix($accountType);

        $lastForPrefix = AccountHolder::query()
            ->where('project_id', $projectId)
            ->where('system_prefix', $prefix)
            ->where('is_deleted', 0)
            ->orderByDesc('id')
            ->first();

        $nextSequence = 1;
        if ($lastForPrefix?->system_no) {
            $numericPart = (int) preg_replace('/\D/', '', (string) $lastForPrefix->system_no);
            $nextSequence = $numericPart > 0 ? $numericPart + 1 : 1;
        }

        return [
            'system_prefix' => $prefix,
            'system_no' => $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT),
        ];
    }
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Return all active countries and currencies associated with the project.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*******  695a0244-3dc7-42ec-84cb-6eda6bcc7625  *******/
    public function index(Request $request)
    {
        $user = Auth::user();
        $project_id = $user->project_id;

        $countries = Country::all();
        $country_arr = [];
        foreach ($countries as $value) {
            $country_arr[$value->id] = $value->country_name;
        }

        $account_holders = AccountHolder::where('project_id', $project_id)
            ->where('is_deleted', 0)
            ->orderByDesc('id')
            ->get()
            ->map(fn(AccountHolder $accountHolder) => $this->normalizeAccountHolder($accountHolder, $country_arr))
            ->values();

        $data['country_arr'] = $country_arr;
        $data['currencies'] = Currency::query()
            ->where('status_active', 1)
            ->orderBy('currency_name')
            ->get(['id', 'currency_code', 'currency_name', 'currency_symbol']);
        $data['account_holders'] = $account_holders;

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'cell_phone' => 'required|string|max:50',
            'email' => 'required|email',
            'account_type' => 'required|integer|between:1,9',
            'prefer_contact_method' => 'nullable|integer|between:1,3',
        ]);

        $user = Auth::user();
        $user_id = $user->id;
        $project_id = $user->project_id;

        $systemIdentity = $this->generateSystemIdentity(
            $project_id,
            $request->input('account_type')
        );

        DB::beginTransaction();

        try {
            $account_holder = AccountHolder::create([
                'project_id' => $project_id,
                'inserted_by' => $user_id,
                'system_prefix' => $systemIdentity['system_prefix'],
                'system_no' => $systemIdentity['system_no'],
                'account_name' => $request->input('account_name'),
                'company_name' => $request->input('company_name'),
                'legal_company_name' => $request->input('legal_company_name'), 'primary_contact_name' => $request->input('primary_contact_name'), 'accounts_email' => $request->input('accounts_email'), 'business_fields' => $request->input('business_fields'), 'supplier_type' => $request->input('supplier_type'), 'credit_limit' => $request->input('credit_limit', 0), 'payment_terms' => $request->input('payment_terms'), 'invoice_terms' => $request->input('invoice_terms'), 'seller_notes' => $request->input('seller_notes'),
                'business_number' => $request->input('business_number'),
                'tax_id_no' => $request->input('tax_id_no'),
                'currency_id' => $request->input('currency_id'),
                'account_type' => $request->integer('account_type'),
                'house_number' => $request->input('house_number'),
                'street_number' => $request->input('street_number'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'zip_code' => $request->input('zip_code'),
                'office_phone' => $request->input('office_phone'),
                'cell_phone' => $request->input('cell_phone'),
                'email' => $request->input('email'),
                'website' => $request->input('website'),
                'prefer_contact_method' => $request->filled('prefer_contact_method')
                    ? $request->integer('prefer_contact_method')
                    : null,
                'linked_transaction_sales' => $request->input('linked_transaction_sales'),
                'linked_transaction_purchase' => $request->input('linked_transaction_purchase'),
                'status_active' => $request->input('status_active', 1),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => '1**' . $account_holder->id . '**' . $systemIdentity['system_no']]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $project_id = $user->project_id;

        $countries = Country::get();
        $country_arr = [];
        foreach ($countries as $value) {
            $country_arr[$value->id] = $value->country_name;
        }

        $data['country_arr'] = $country_arr;
        $data['currencies'] = Currency::query()
            ->where('status_active', 1)
            ->orderBy('currency_name')
            ->get(['id', 'currency_code', 'currency_name', 'currency_symbol']);

        $account_holder = AccountHolder::where('id', $id)
            ->where('project_id', $project_id)
            ->first();

        if ($account_holder) {
            $account_holder = $this->normalizeAccountHolder($account_holder, $country_arr);
        }

        $data['account_holder'] = $account_holder;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'cell_phone' => 'required|string|max:50',
            'email' => 'required|email',
            'account_type' => 'required|integer|between:1,9',
            'prefer_contact_method' => 'nullable|integer|between:1,3',
        ]);

        $user = Auth::user();
        $user_id = $user->id;
        $project_id = $user->project_id;

        DB::beginTransaction();

        try {
            $account_holder = AccountHolder::where('id', $id)
                ->where('project_id', $project_id)
                ->firstOrFail();

            $account_holder->update([
                'updated_by' => $user_id,
                'account_name' => $request->input('account_name'),
                'company_name' => $request->input('company_name'),
                'legal_company_name' => $request->input('legal_company_name'), 'primary_contact_name' => $request->input('primary_contact_name'), 'accounts_email' => $request->input('accounts_email'), 'business_fields' => $request->input('business_fields'), 'supplier_type' => $request->input('supplier_type'), 'credit_limit' => $request->input('credit_limit', 0), 'payment_terms' => $request->input('payment_terms'), 'invoice_terms' => $request->input('invoice_terms'), 'seller_notes' => $request->input('seller_notes'),
                'business_number' => $request->input('business_number'),
                'tax_id_no' => $request->input('tax_id_no'),
                'currency_id' => $request->input('currency_id'),
                'account_type' => $request->integer('account_type'),
                'house_number' => $request->input('house_number'),
                'street_number' => $request->input('street_number'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'zip_code' => $request->input('zip_code'),
                'office_phone' => $request->input('office_phone'),
                'cell_phone' => $request->input('cell_phone'),
                'email' => $request->input('email'),
                'website' => $request->input('website'),
                'prefer_contact_method' => $request->filled('prefer_contact_method')
                    ? $request->integer('prefer_contact_method')
                    : null,
                'linked_transaction_sales' => $request->input('linked_transaction_sales'),
                'linked_transaction_purchase' => $request->input('linked_transaction_purchase'),
                'status_active' => $request->input('status_active', 1),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => '1**' . $id . '**']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $project_id = $user->project_id;

        $account_holder = AccountHolder::where('id', $id)
            ->where('project_id', $project_id)
            ->firstOrFail();

        $account_holder->update(['is_deleted' => 1, 'status_active' => 0]);

        return response()->json(['success' => true]);
    }
}
