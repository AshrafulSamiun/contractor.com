<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plan feature enforcement
    |--------------------------------------------------------------------------
    | Temporary switch: when false, plan-based feature gating is bypassed.
    | Set PLAN_FEATURE_ENFORCED=true to re-enable strict plan checks.
    */
    'enforce' => env('PLAN_FEATURE_ENFORCED', false),

    'default_plan' => 'standard',

    'plans' => [
        'basic' => 1,
        'standard' => 2,
        'enterprise' => 3,
    ],

    // Feature => minimum required plan
    'features' => [
        'profiles_core' => 'standard',
        'storage_management' => 'standard',
        'pickup_management' => 'standard',
        'pickup_external_locker' => 'enterprise',
        'locker_access_management' => 'enterprise',
        'user_management' => 'enterprise',
        'workforce_management' => 'enterprise',
        'reporting' => 'enterprise',
        'notification_center' => 'enterprise',
    ],
];
