<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    private const FACILITY_TYPE_MAP = [
        '1' => 'Clinics',
        '2' => 'Colleges',
        '3' => 'Commercial Building',
        '4' => 'Condominiums',
        '5' => 'Corporate Offices',
        '6' => 'Co-working Spaces',
        '7' => 'Government Offices',
        '8' => 'Hospitals',
        '9' => 'Hostels',
        '10' => 'Hotels',
        '11' => 'Municipal Buildings',
        '12' => 'Nursing Homes',
        '13' => 'Office Buildings',
        '14' => 'Police Stations',
        '15' => 'Property Management',
        '16' => 'Public Libraries',
        '17' => 'Residential Buildings',
        '18' => 'Schools',
        '19' => 'Senior Living Communities',
        '20' => 'Serviced Apartments',
        '21' => 'Shopping Malls',
        '22' => 'Universities',
    ];

    public function index(Request $request)
    {
        $query = Facility::query()->where('user_id', $request->user()->id);

        if ($request->filled('facility_name')) {
            $query->where('facility_name', 'like', '%' . $request->facility_name . '%');
        }
        if ($request->filled('facility_type')) {
            $facilityTypeInput = trim((string) $request->facility_type);
            $normalizedFacilityType = (string) ($this->normalizeFacilityTypeValue($facilityTypeInput) ?? '');
            $labelById = self::FACILITY_TYPE_MAP[$normalizedFacilityType] ?? null;

            $query->where(function ($sub) use ($facilityTypeInput, $normalizedFacilityType, $labelById) {
                $sub->where('facility_type', 'like', '%' . $facilityTypeInput . '%');

                if ($normalizedFacilityType !== '' && $normalizedFacilityType !== $facilityTypeInput) {
                    $sub->orWhere('facility_type', 'like', '%' . $normalizedFacilityType . '%');
                }

                if ($labelById) {
                    $sub->orWhere('facility_type', 'like', '%' . $labelById . '%');
                }
            });
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $facilities = $query->orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'data' => $facilities]);
    }

    public function show(Request $request, Facility $facility)
    {
        if ($response = $this->denyUnlessOwns($request, $facility)) {
            return $response;
        }

        return response()->json(['success' => true, 'data' => $facility]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'facility_type' => $this->normalizeFacilityTypeValue($request->input('facility_type')),
            'office_phone' => $this->normalizePhoneValue($request->input('office_phone')),
            'mobile_phone' => $this->normalizePhoneValue($request->input('mobile_phone')),
        ]);
        $data = $request->validate($this->facilityValidationRules(), $this->facilityValidationMessages());
        $data = $this->normalizeCountryPayload($data);

        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'Active';

        $facility = Facility::create($data);

        return response()->json(['success' => true, 'data' => $facility], 201);
    }

    public function update(Request $request, Facility $facility)
    {
        if ($response = $this->denyUnlessOwns($request, $facility)) {
            return $response;
        }

        $request->merge([
            'facility_type' => $this->normalizeFacilityTypeValue($request->input('facility_type')),
            'office_phone' => $this->normalizePhoneValue($request->input('office_phone')),
            'mobile_phone' => $this->normalizePhoneValue($request->input('mobile_phone')),
        ]);
        $data = $request->validate($this->facilityValidationRules(), $this->facilityValidationMessages());
        $data = $this->normalizeCountryPayload($data);

        $facility->update($data);

        return response()->json(['success' => true, 'data' => $facility]);
    }

    public function destroy(Request $request, Facility $facility)
    {
        if ($response = $this->denyUnlessOwns($request, $facility)) {
            return $response;
        }

        $facility->delete();

        return response()->json(['success' => true]);
    }

    protected function normalizeCountryPayload(array $data): array
    {
        $country = null;

        if (!empty($data['country_id'])) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->find($data['country_id']);
        } elseif (!empty($data['country'])) {
            $country = Country::query()
                ->select(['id', 'country_name'])
                ->where('country_name', $data['country'])
                ->first();
        }

        if ($country) {
            $data['country_id'] = $country->id;
            $data['country'] = $country->country_name;
            return $data;
        }

        if (empty($data['country']) && empty($data['country_id'])) {
            $data['country'] = null;
            $data['country_id'] = null;
        }

        return $data;
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

    protected function normalizeFacilityTypeValue(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        if (array_key_exists($trimmed, self::FACILITY_TYPE_MAP)) {
            return $trimmed;
        }

        $normalized = strtolower($trimmed);
        foreach (self::FACILITY_TYPE_MAP as $id => $label) {
            if (strtolower($label) === $normalized) {
                return $id;
            }
        }

        return $trimmed;
    }

    protected function facilityValidationRules(): array
    {
        return [
            'facility_name' => ['required', 'string', 'max:255'],
            'facility_type' => ['required', 'string', Rule::in(array_keys(self::FACILITY_TYPE_MAP))],
            'unit_no' => ['nullable', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'country' => ['nullable', 'string', 'max:255'],
            'office_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/', 'required_without:email'],
            'mobile_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:office_phone'],
            'fax' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function facilityValidationMessages(): array
    {
        return [
            'facility_type.required' => 'Facility type is required.',
            'facility_type.in' => 'Select a valid facility type from the dropdown.',
            'street.required' => 'Street is required.',
            'city.required' => 'City is required.',
            'country_id.required' => 'Country is required.',
            'office_phone.required_without' => 'Provide at least Office Phone or Email.',
            'email.required_without' => 'Provide at least Office Phone or Email.',
            'office_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'mobile_phone.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ];
    }
}
