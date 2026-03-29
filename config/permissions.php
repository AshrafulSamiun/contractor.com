<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission enforcement
    |--------------------------------------------------------------------------
    | Temporary switch: when false, permission checks are bypassed.
    | Set PERMISSION_ENFORCED=true to re-enable strict permission checks.
    */
    'enforce' => env('PERMISSION_ENFORCED', true),

    /*
    |--------------------------------------------------------------------------
    | Todo temporary open access
    |--------------------------------------------------------------------------
    | Test switch: when true, all authenticated users can create/update/review/
    | delete To-Do tasks within their company scope.
    */
    'todo_open_access' => env('TODO_OPEN_ACCESS', true),

    'modules' => [
        'users' => ['read', 'create', 'edit', 'delete', 'export'],
        'reports' => ['read', 'export'],
        'workforce' => ['read', 'create', 'edit', 'approve', 'export'],
        'settings' => ['read', 'edit'],
        'notifications' => ['read', 'send'],
        'profiles' => ['read', 'create', 'edit', 'delete'],
        'pickup' => ['read', 'create', 'edit', 'delete', 'export'],
        'parcels' => ['read', 'create', 'edit', 'delete', 'export'],
        'email' => ['read', 'create', 'edit', 'delete', 'send'],
        'calendar' => ['read', 'create', 'edit', 'delete'],
        'announcements' => ['read', 'create', 'edit', 'delete', 'approve', 'publish'],
        'plans' => ['read', 'edit'],
        'account' => ['read', 'edit'],
    ],

    // Default permission map used when database entries are missing.
    'defaults' => [
        'admin' => [
            '*' => ['*'],
        ],
        'manager' => [
            'users' => ['read', 'edit', 'export'],
            'reports' => ['read', 'export'],
            'workforce' => ['read', 'create', 'edit', 'approve', 'export'],
            'settings' => ['read'],
            'notifications' => ['read'],
            'profiles' => ['read', 'create', 'edit'],
            'pickup' => ['read', 'create', 'edit', 'export'],
            'parcels' => ['read', 'create', 'edit', 'export'],
            'email' => ['read', 'create', 'edit', 'send'],
            'calendar' => ['read', 'create', 'edit'],
            'announcements' => ['read', 'create', 'edit', 'publish'],
            'plans' => ['read'],
            'account' => ['read', 'edit'],
        ],
        'staff' => [
            'reports' => ['read'],
            'workforce' => ['read', 'create', 'edit'],
            'notifications' => ['read'],
            'profiles' => ['read'],
            'pickup' => ['read', 'create', 'edit'],
            'parcels' => ['read', 'create', 'edit'],
            'email' => ['read', 'create', 'edit', 'send'],
            'calendar' => ['read', 'create', 'edit'],
            'announcements' => ['read'],
            'account' => ['read', 'edit'],
        ],
    ],
];
