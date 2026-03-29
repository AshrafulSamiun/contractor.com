<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $query = Courier::query()->where('user_id', $request->user()->id);

        if ($request->filled('company_name')) {
            $query->where('company_name', 'like', '%' . $request->company_name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('phone')) {
            $this->applyPhoneSearchFilter($query, $request->input('phone'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $page = max((int) $request->integer('page', 1), 1);
        $items = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, Courier $courier)
    {
        if ($response = $this->denyUnlessOwns($request, $courier)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $courier]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhoneValue($request->input('phone')),
        ]);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);

        $data['user_id'] = $request->user()->id;

        $courier = Courier::create($data);

        return response()->json(['success' => true, 'data' => $courier], 201);
    }

    public function update(Request $request, Courier $courier)
    {
        if ($response = $this->denyUnlessOwns($request, $courier)) {
            return $response;
        }

        $request->merge([
            'phone' => $this->normalizePhoneValue($request->input('phone')),
        ]);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);

        $courier->update($data);

        return response()->json(['success' => true, 'data' => $courier]);
    }

    public function destroy(Request $request, Courier $courier)
    {
        if ($response = $this->denyUnlessOwns($request, $courier)) {
            return $response;
        }

        $courier->delete();

        return response()->json(['success' => true]);
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

    protected function applyPhoneSearchFilter(mixed $query, mixed $phoneInput): void
    {
        $raw = is_string($phoneInput) ? trim($phoneInput) : '';
        if ($raw === '') {
            return;
        }

        $normalized = $this->normalizePhoneValue($raw);
        $digits = preg_replace('/\D/', '', $raw);
        $hasNormalized = is_string($normalized) && $normalized !== '';
        $hasDigits = is_string($digits) && $digits !== '';

        $query->where(function ($subQuery) use ($raw, $normalized, $digits, $hasNormalized, $hasDigits) {
            if ($hasNormalized) {
                $subQuery->where('phone', 'like', '%' . $normalized . '%');
            }

            if ($hasDigits) {
                if ($hasNormalized) {
                    $subQuery->orWhereRaw("REPLACE(phone, '+', '') like ?", ['%' . $digits . '%']);
                } else {
                    $subQuery->whereRaw("REPLACE(phone, '+', '') like ?", ['%' . $digits . '%']);
                }
            }

            if (!$hasNormalized && !$hasDigits) {
                $subQuery->where('phone', 'like', '%' . $raw . '%');
            }
        });
    }
}
