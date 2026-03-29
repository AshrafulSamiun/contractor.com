<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecipientController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipient::query()->where('user_id', $request->user()->id);

        if ($request->filled('recipient_name')) {
            $query->where('recipient_name', 'like', '%' . $request->recipient_name . '%');
        }
        if ($request->filled('facility_name')) {
            $query->where('facility_name', 'like', '%' . $request->facility_name . '%');
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', (int) $request->facility_id);
        }
        if ($request->filled('recipient_type')) {
            $query->whereJsonContains('recipient_types', $request->recipient_type);
        }
        if ($request->filled('phone')) {
            $this->applyPhoneSearchFilter($query, $request->input('phone'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $items = $query->orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(Request $request, Recipient $recipient)
    {
        if ($response = $this->denyUnlessOwns($request, $recipient)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $recipient]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhoneValue($request->input('phone')),
        ]);

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_types' => ['required', 'array', 'min:1'],
            'recipient_types.*' => ['string', 'max:50'],
            'facility_id' => [
                'nullable',
                'integer',
                'required_without:facility_name',
                Rule::exists('facilities', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ],
            'facility_name' => ['nullable', 'string', 'max:255', 'required_without:facility_id'],
            'floor_no' => ['nullable', 'string', 'max:255'],
            'residential_suite_no' => ['nullable', 'string', 'max:255'],
            'commercial_unit_no' => ['nullable', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'store' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'facility_id.required_without' => 'Facility is required.',
            'facility_id.exists' => 'Select a valid facility.',
            'facility_name.required_without' => 'Facility is required.',
        ]);

        $data = $this->syncFacilityFields($request, $data);
        $data['user_id'] = $request->user()->id;

        $recipient = Recipient::create($data);

        return response()->json(['success' => true, 'data' => $recipient], 201);
    }

    public function update(Request $request, Recipient $recipient)
    {
        if ($response = $this->denyUnlessOwns($request, $recipient)) {
            return $response;
        }

        $request->merge([
            'phone' => $this->normalizePhoneValue($request->input('phone')),
        ]);

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_types' => ['required', 'array', 'min:1'],
            'recipient_types.*' => ['string', 'max:50'],
            'facility_id' => [
                'nullable',
                'integer',
                'required_without:facility_name',
                Rule::exists('facilities', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ],
            'facility_name' => ['nullable', 'string', 'max:255', 'required_without:facility_id'],
            'floor_no' => ['nullable', 'string', 'max:255'],
            'residential_suite_no' => ['nullable', 'string', 'max:255'],
            'commercial_unit_no' => ['nullable', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'store' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'facility_id.required_without' => 'Facility is required.',
            'facility_id.exists' => 'Select a valid facility.',
            'facility_name.required_without' => 'Facility is required.',
        ]);

        $data = $this->syncFacilityFields($request, $data);
        $recipient->update($data);

        return response()->json(['success' => true, 'data' => $recipient]);
    }

    public function destroy(Request $request, Recipient $recipient)
    {
        if ($response = $this->denyUnlessOwns($request, $recipient)) {
            return $response;
        }

        $recipient->delete();

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

    protected function syncFacilityFields(Request $request, array $data): array
    {
        $facilityId = $data['facility_id'] ?? null;

        $facility = null;

        if ($facilityId) {
            $facility = Facility::query()
                ->where('user_id', $request->user()->id)
                ->find($facilityId);
        } elseif (!empty($data['facility_name'])) {
            $facility = Facility::query()
                ->where('user_id', $request->user()->id)
                ->where('facility_name', trim((string) $data['facility_name']))
                ->first();
        }

        if ($facility) {
            $data['facility_id'] = (int) $facility->id;
            $data['facility_name'] = $facility->facility_name;
        } else {
            $data['facility_id'] = $facilityId ? (int) $facilityId : null;
            $data['facility_name'] = !empty($data['facility_name']) ? trim((string) $data['facility_name']) : null;
        }

        return $data;
    }
}
