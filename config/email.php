<?php

return [
    'attachments' => [
        'max_file_mb' => 5,
        'max_total_mb' => 25,
        'max_user_total_mb' => 200,
        'blocked_extensions' => [
            'exe',
            'bat',
            'cmd',
            'js',
            'vbs',
            'sh',
            'php',
        ],
        'virus_scan_enabled' => false,
        'virus_scan_command' => 'clamscan --no-summary',
        'allowed_mimes' => [
            'application/pdf',
            'image/png',
            'image/jpeg',
            'image/webp',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],
    'retention' => [
        'trash_days' => 30,
    ],
    'webhooks' => [
        'token' => env('EMAIL_WEBHOOK_TOKEN', null),
    ],
];
