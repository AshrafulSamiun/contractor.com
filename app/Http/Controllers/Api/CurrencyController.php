<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $currencies = Currency::query()
            ->orderBy('currency_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

    public function show(Request $request, Currency $currency)
    {
        return response()->json([
            'success' => true,
            'data' => $currency,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'currency_code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies', 'currency_code'),
            ],
            'currency_name' => ['required', 'string', 'max:120'],
            'currency_symbol' => ['nullable', 'string', 'max:20'],
            'status_active' => ['required', 'boolean'],
        ]);

        $currency = Currency::create([
            ...$data,
            'inserted_by' => $user->id,
            'updated_by' => $user->id,
            'currency_code' => strtoupper(trim($data['currency_code'])),
        ]);

        return response()->json([
            'success' => true,
            'data' => $currency,
        ], 201);
    }

    public function update(Request $request, Currency $currency)
    {
        $user = $request->user();

        $data = $request->validate([
            'currency_code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies', 'currency_code')
                    ->ignore($currency->id),
            ],
            'currency_name' => ['required', 'string', 'max:120'],
            'currency_symbol' => ['nullable', 'string', 'max:20'],
            'status_active' => ['required', 'boolean'],
        ]);

        $currency->update([
            ...$data,
            'updated_by' => $user->id,
            'currency_code' => strtoupper(trim($data['currency_code'])),
        ]);

        return response()->json([
            'success' => true,
            'data' => $currency->fresh(),
        ]);
    }

    public function destroy(Request $request, Currency $currency)
    {
        $currency->update([
            'updated_by' => $request->user()->id,
            'status_active' => false,
        ]);

        $currency->delete();

        return response()->json(['success' => true]);
    }
}
