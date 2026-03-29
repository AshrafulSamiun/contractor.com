<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class LanguageSettingsController extends Controller
{
    protected function defaults(): array
    {
        return [
            'timezone' => 'UTC',
            'date_format' => 'MM/DD/YYYY',
            'time_format' => '12h',
            'week_start' => 'Monday',
            'theme_mode' => 'light',
            'theme_accent' => 'blue',
            'language_code' => 'en',
        ];
    }

    public function show(Request $request)
    {
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            $this->defaults()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'language_code' => $settings->language_code ?: 'en',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'language_code' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2,3}(-[a-z]{2,3})?$/i'],
        ]);
        $data['language_code'] = strtolower(trim($data['language_code']));

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            $this->defaults()
        );

        $settings->update($data);

        $setup = AccountSetup::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_step' => 1]
        );
        $payload = $setup->data ?? [];
        $profile = $payload['profile'] ?? [];
        $profile['preferred_language'] = $settings->language_code;
        $payload['profile'] = $profile;
        $setup->data = $payload;
        $setup->save();

        return response()->json([
            'success' => true,
            'data' => [
                'language_code' => $settings->language_code,
            ],
        ]);
    }
}
