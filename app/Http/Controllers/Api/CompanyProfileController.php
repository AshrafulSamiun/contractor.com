<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyProfileController extends Controller
{
    private function setup(Request $request): AccountSetup
    {
        $user = $request->user();
        abort_unless($user->project_id, 404, 'No company profile is linked to this account.');

        return AccountSetup::query()
            ->whereKey($user->project_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    public function show(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->setup($request)]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_location' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:150'],
            'contact_website' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:120'],
            'contact_business_email' => ['nullable', 'email', 'max:255'],
            'contact_primary_phone' => ['nullable', 'string', 'max:30'],
            'business_registration_number' => ['nullable', 'string', 'max:120'],
            'incorporation_date' => ['nullable', 'date'],
            'registration_country' => ['nullable', 'string', 'max:100'],
            'registration_province' => ['nullable', 'string', 'max:100'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'company_state' => ['nullable', 'string', 'max:100'],
            'company_zip' => ['nullable', 'string', 'max:30'],
            'company_country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:120'],
            'company_status' => ['required', Rule::in(['Active', 'Inactive'])],
            'company_profile_confirmation' => ['required', 'boolean'],
        ]);

        if (!empty($validated['company_country_id'])) {
            $country = Country::find($validated['company_country_id']);
            $validated['company_country'] = $country?->country_name;
        }

        $setup = $this->setup($request);
        $data = $setup->data ?? [];
        $form = $data['setup_form'] ?? [];
        $form['business_registration_number'] = $validated['business_registration_number'] ?? null;
        $form['tax_number'] = $validated['tax_number'] ?? null;
        $form['company_profile_confirmation'] = $validated['company_profile_confirmation'];
        $data['setup_form'] = $form;
        unset($validated['business_registration_number'], $validated['tax_number'], $validated['company_profile_confirmation']);

        // These are legacy setup fields which are intentionally not mass assignable.
        $setup->registration_country = $validated['registration_country'] ?? null;
        $setup->registration_province = $validated['registration_province'] ?? null;
        unset($validated['registration_country'], $validated['registration_province']);

        $setup->fill($validated);
        $setup->data = $data;
        $setup->save();

        return response()->json(['success' => true, 'data' => $setup->fresh()]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => ['required', 'file', 'mimes:jpg,jpeg,png,svg', 'max:5120']]);
        $setup = $this->setup($request);
        $path = $request->file('logo')->store('account-logos', 'public');
        $setup->update(['company_logo_path' => $path, 'company_logo_url' => Storage::disk('public')->url($path)]);

        return response()->json(['success' => true, 'data' => ['url' => $setup->company_logo_url]]);
    }
}
