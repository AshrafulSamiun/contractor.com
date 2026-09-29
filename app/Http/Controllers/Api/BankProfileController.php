<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankProfile;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = BankProfile::query()->where('project_id', $request->user()->project_id);
        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(fn ($q) => $q->where('bank_name', 'like', "%{$search}%")->orWhere('bank_no', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%"));
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function options()
    {
        return response()->json(['success' => true, 'data' => [
            'currencies' => Currency::query()->where('status_active', 1)->orderBy('currency_name')->get(['id', 'currency_code', 'currency_name', 'currency_symbol']),
        ]]);
    }

    public function store(Request $request)
    {
        $projectId = $request->user()->project_id;
        $bank = DB::transaction(function () use ($request, $projectId) {
            return BankProfile::create($this->validated($request) + [
                'project_id' => $projectId,
                'bank_no' => $this->nextBankNo($projectId),
                'created_by' => $request->user()->id,
            ]);
        });

        return response()->json(['success' => true, 'data' => $bank], 201);
    }

    public function show(Request $request, BankProfile $bank)
    {
        $this->authorizeBank($request, $bank);
        return response()->json(['success' => true, 'data' => $bank]);
    }

    public function update(Request $request, BankProfile $bank)
    {
        $this->authorizeBank($request, $bank);
        $bank->update($this->validated($request) + ['updated_by' => $request->user()->id]);
        return response()->json(['success' => true, 'data' => $bank->fresh()]);
    }

    public function destroy(Request $request, BankProfile $bank)
    {
        $this->authorizeBank($request, $bank);
        $bank->delete();
        return response()->json(['success' => true]);
    }

    private function authorizeBank(Request $request, BankProfile $bank): void
    {
        abort_unless((int) $bank->project_id === (int) $request->user()->project_id, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'status_active' => ['required', 'boolean'], 'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'linked_gl_account_id' => ['nullable', 'integer'], 'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:120'], 'opening_balance' => ['nullable', 'numeric'],
            'opening_balance_date' => ['nullable', 'date'], 'contact_person' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);
    }

    private function nextBankNo(?int $projectId): string
    {
        $last = BankProfile::query()
            ->where('project_id', $projectId)
            ->where('bank_no', 'like', 'BK-%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('bank_no');

        $sequence = $last ? (int) substr($last, 3) + 1 : 1;

        return 'BK-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
