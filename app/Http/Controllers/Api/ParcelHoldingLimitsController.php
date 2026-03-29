<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class ParcelHoldingLimitsController extends Controller
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
                'theme_mode' => 'system',
                'theme_accent' => 'blue',
                'holding_default_days' => 7,
                'holding_max_days' => 30,
                'holding_reminder_days' => 2,
                'holding_auto_expire' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'holding_default_days' => $settings->holding_default_days ?? 7,
                'holding_max_days' => $settings->holding_max_days ?? 30,
                'holding_reminder_days' => $settings->holding_reminder_days ?? 2,
                'holding_auto_expire' => (bool) ($settings->holding_auto_expire ?? true),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'holding_default_days' => ['required', 'integer', 'min:1', 'max:365'],
            'holding_max_days' => ['required', 'integer', 'min:1', 'max:365'],
            'holding_reminder_days' => ['required', 'integer', 'min:0', 'max:90'],
            'holding_auto_expire' => ['required', 'boolean'],
        ]);

        if ($data['holding_max_days'] < $data['holding_default_days']) {
            return response()->json([
                'message' => 'Max holding days must be greater than or equal to default holding days.',
                'errors' => [
                    'holding_max_days' => ['Max holding days must be greater than or equal to default holding days.'],
                ],
            ], 422);
        }

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'timezone' => 'UTC',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'week_start' => 'Monday',
                'theme_mode' => 'system',
                'theme_accent' => 'blue',
                'holding_default_days' => 7,
                'holding_max_days' => 30,
                'holding_reminder_days' => 2,
                'holding_auto_expire' => true,
            ]
        );

        $settings->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'holding_default_days' => $settings->holding_default_days,
                'holding_max_days' => $settings->holding_max_days,
                'holding_reminder_days' => $settings->holding_reminder_days,
                'holding_auto_expire' => (bool) $settings->holding_auto_expire,
            ],
        ]);
    }
}
