<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountHolder;
use App\Models\BankProfile;
use App\Models\Country;
use App\Models\CreditCard;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditCardController extends Controller
{
    public function index(Request $request)
    {
        $cards = CreditCard::query()->where('project_id', $request->user()->project_id)
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where('card_nickname', 'like', "%{$search}%")->orWhere('card_no', 'like', "%{$search}%")->orWhere('card_last_four', 'like', "%{$search}%");
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status_active', $request->boolean('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('card_type', $request->string('type')->trim()))
            ->latest()->get();

        return response()->json(['success' => true, 'data' => $cards]);
    }

    public function options(Request $request)
    {
        $projectId = $request->user()->project_id;
        return response()->json(['success' => true, 'data' => [
            'currencies' => Currency::where('status_active', true)->orderBy('currency_name')->get(['id', 'currency_code', 'currency_name']),
            'banks' => BankProfile::where('project_id', $projectId)->where('status_active', true)->orderBy('bank_name')->get(['id', 'bank_name', 'account_name']),
            'accounts' => AccountHolder::where('project_id', $projectId)->where('is_deleted', false)->orderBy('account_name')->get(['id', 'account_name', 'system_no']),
            'countries' => Country::orderBy('country_name')->get(['id', 'country_name']),
            'users' => User::where('project_id', $projectId)->orderBy('name')->get(['id', 'name', 'email']),
        ]]);
    }

    public function show(Request $request, CreditCard $creditCard)
    {
        $this->authorizeCard($request, $creditCard);
        return response()->json(['success' => true, 'data' => $creditCard]);
    }

    public function store(Request $request)
    {
        $projectId = $request->user()->project_id;
        $card = DB::transaction(function () use ($request, $projectId) {
            $data = $this->validated($request, true);
            $this->validateProjectRelations($data, $projectId);
            if ($data['is_default']) CreditCard::where('project_id', $projectId)->update(['is_default' => false]);
            unset($data['cvv']); // CVV must never be retained after validation.
            $data['card_number'] = preg_replace('/\D/', '', $data['card_number']);
            $data['card_last_four'] = substr($data['card_number'], -4);
            return CreditCard::create($data + ['project_id' => $projectId, 'card_no' => $this->nextCardNo($projectId), 'created_by' => $request->user()->id]);
        });
        return response()->json(['success' => true, 'data' => $card, 'message' => 'Credit card created.'], 201);
    }

    public function update(Request $request, CreditCard $creditCard)
    {
        $this->authorizeCard($request, $creditCard);
        DB::transaction(function () use ($request, $creditCard) {
            $data = $this->validated($request, false);
            $this->validateProjectRelations($data, $creditCard->project_id);
            if ($data['is_default']) CreditCard::where('project_id', $creditCard->project_id)->where('id', '<>', $creditCard->id)->update(['is_default' => false]);
            unset($data['cvv']); // CVV must never be retained after validation.
            if (!empty($data['card_number'])) {
                $data['card_number'] = preg_replace('/\D/', '', $data['card_number']);
                $data['card_last_four'] = substr($data['card_number'], -4);
            } else unset($data['card_number']);
            $creditCard->update($data + ['updated_by' => $request->user()->id]);
        });
        return response()->json(['success' => true, 'data' => $creditCard->fresh(), 'message' => 'Credit card updated.']);
    }

    public function destroy(Request $request, CreditCard $creditCard)
    {
        $this->authorizeCard($request, $creditCard);
        $creditCard->delete();
        return response()->json(['success' => true, 'message' => 'Credit card deleted.']);
    }

    private function validated(Request $request, bool $creating): array
    {
        $rules = [
            'card_nickname' => ['required', 'string', 'max:255'], 'card_number' => [$creating ? 'required' : 'nullable', 'string', 'regex:/^[0-9 -]{13,23}$/'], 'cvv' => [$creating ? 'required' : 'nullable', 'digits_between:3,4'],
            'cardholder_name' => ['required', 'string', 'max:255'], 'card_type' => ['required', 'in:Visa,Mastercard,American Express,Discover,Other'],
            'card_network' => ['required', 'string', 'max:50'], 'expiry_date' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'credit_limit' => ['required', 'numeric', 'min:0'], 'current_balance' => ['nullable', 'numeric', 'min:0'],
            'billing_day' => ['required', 'integer', 'between:1,31'], 'due_day' => ['required', 'integer', 'between:1,31'], 'pay_day' => ['required', 'integer', 'between:1,31'],
            'status_active' => ['required', 'boolean'], 'card_category' => ['required', 'in:Corporate,Business,Personal'],
            'currency_id' => ['required', 'exists:currencies,id'], 'bank_profile_id' => ['nullable', 'integer'], 'account_holder_id' => ['nullable', 'integer'],
            'annual_fee' => ['nullable', 'numeric', 'min:0'], 'payment_due_day' => ['nullable', 'integer', 'between:1,31'], 'grace_period_days' => ['nullable', 'integer', 'between:0,365'],
            'minimum_payment_percent' => ['nullable', 'numeric', 'between:0,100'], 'interest_rate' => ['nullable', 'numeric', 'between:0,100'],
            'billing_address' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'], 'country_id' => ['nullable', 'exists:countries,id'], 'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'], 'department' => ['nullable', 'string', 'max:100'], 'assigned_to' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'], 'is_default' => ['required', 'boolean'],
        ];
        return $request->validate($rules);
    }

    private function validateProjectRelations(array $data, $projectId): void
    {
        abort_unless(empty($data['bank_profile_id']) || BankProfile::where('project_id', $projectId)->whereKey($data['bank_profile_id'])->exists(), 422);
        abort_unless(empty($data['account_holder_id']) || AccountHolder::where('project_id', $projectId)->whereKey($data['account_holder_id'])->exists(), 422);
        abort_unless(empty($data['assigned_to']) || User::where('project_id', $projectId)->whereKey($data['assigned_to'])->exists(), 422);
    }

    private function authorizeCard(Request $request, CreditCard $creditCard): void { abort_unless((int) $creditCard->project_id === (int) $request->user()->project_id, 404); }
    private function nextCardNo($projectId): string { $last = CreditCard::where('project_id', $projectId)->lockForUpdate()->latest('id')->value('card_no'); return 'CC-'.str_pad((string) (($last ? (int) substr($last, 3) : 0) + 1), 3, '0', STR_PAD_LEFT); }
}
