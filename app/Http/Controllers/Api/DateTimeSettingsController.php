<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DateTimeSettingsController extends Controller
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
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'timezone' => ['required', 'string', 'max:80'],
            'date_format' => ['required', Rule::in(['MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD'])],
            'time_format' => ['required', Rule::in(['12h', '24h'])],
            'week_start' => ['required', Rule::in(['Monday', 'Sunday'])],
        ]);

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'timezone' => 'UTC',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'week_start' => 'Monday',
            ]
        );

        $settings->update($data);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
