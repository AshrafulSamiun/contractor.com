<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemeSettingsController extends Controller
{
    public function show(Request $request)
    {
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'timezone' => 'UTC',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'week_start' => 'Monday',
                'theme_mode' => 'light',
                'theme_accent' => 'blue',
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'theme_mode' => $settings->theme_mode ?? 'light',
                'theme_accent' => $settings->theme_accent ?? 'blue',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme_mode' => ['required', Rule::in(['system', 'light', 'dark'])],
            'theme_accent' => ['required', Rule::in(['blue', 'teal', 'indigo', 'emerald', 'orange'])],
        ]);

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'timezone' => 'UTC',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'week_start' => 'Monday',
                'theme_mode' => 'light',
                'theme_accent' => 'blue',
            ]
        );

        $settings->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'theme_mode' => $settings->theme_mode,
                'theme_accent' => $settings->theme_accent,
            ],
        ]);
    }
}
