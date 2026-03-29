<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::query()->where('user_id', $request->user()->id);

        if ($request->filled('seller_name')) {
            $query->where('seller_name', 'like', '%' . $request->seller_name . '%');
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

    public function show(Request $request, Seller $seller)
    {
        if ($response = $this->denyUnlessOwns($request, $seller)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $seller]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'seller_name' => $this->normalizeTextValue($request->input('seller_name'), false),
            'email' => $this->normalizeTextValue($request->input('email')),
            'phone' => $this->normalizePhoneValue($request->input('phone')),
            'website' => $this->normalizeTextValue($request->input('website')),
            'notes' => $this->normalizeTextValue($request->input('notes')),
        ]);

        $data = $request->validate([
            'seller_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('sellers', 'seller_name')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/', 'required_without:email'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'seller_name.required' => 'Seller name is required.',
            'seller_name.min' => 'Seller name must be at least 2 characters.',
            'seller_name.unique' => 'This seller name already exists.',
            'email.required_without' => 'Email or phone is required.',
            'email.email' => 'Provide a valid email address.',
            'phone.required_without' => 'Phone or email is required.',
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'website.url' => 'Website must be a valid URL with http:// or https://.',
        ]);

        $data['user_id'] = $request->user()->id;

        $seller = Seller::create($data);

        return response()->json(['success' => true, 'data' => $seller], 201);
    }

    public function update(Request $request, Seller $seller)
    {
        if ($response = $this->denyUnlessOwns($request, $seller)) {
            return $response;
        }

        $request->merge([
            'seller_name' => $this->normalizeTextValue($request->input('seller_name'), false),
            'email' => $this->normalizeTextValue($request->input('email')),
            'phone' => $this->normalizePhoneValue($request->input('phone')),
            'website' => $this->normalizeTextValue($request->input('website')),
            'notes' => $this->normalizeTextValue($request->input('notes')),
        ]);

        $data = $request->validate([
            'seller_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('sellers', 'seller_name')
                    ->where(fn ($q) => $q->where('user_id', $request->user()->id))
                    ->ignore($seller->id),
            ],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/', 'required_without:email'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'seller_name.required' => 'Seller name is required.',
            'seller_name.min' => 'Seller name must be at least 2 characters.',
            'seller_name.unique' => 'This seller name already exists.',
            'email.required_without' => 'Email or phone is required.',
            'email.email' => 'Provide a valid email address.',
            'phone.required_without' => 'Phone or email is required.',
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'website.url' => 'Website must be a valid URL with http:// or https://.',
        ]);

        $seller->update($data);

        return response()->json(['success' => true, 'data' => $seller]);
    }

    public function destroy(Request $request, Seller $seller)
    {
        if ($response = $this->denyUnlessOwns($request, $seller)) {
            return $response;
        }

        $seller->delete();

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

    protected function normalizeTextValue(mixed $value, bool $nullable = true): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return $nullable ? null : '';
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return $nullable ? null : '';
        }

        return $trimmed;
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
