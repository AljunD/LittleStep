<?php

return [
    'guardian_default_password' => env('GUARDIAN_DEFAULT_PASSWORD', 'Password123@'),

    'guardian_app_login_url' => env('GUARDIAN_APP_LOGIN_URL', 'littlestep://login'),

    // Days to keep System Logs entries before the daily prune removes them. Empty keeps them forever.
    'audit_log_retention_days' => env('AUDIT_LOG_RETENTION_DAYS') ?: null,

    // Shown on the public privacy policy (/privacy): who is responsible for the data and how parents reach them.
    'privacy' => [
        'controller' => env('PRIVACY_CONTROLLER_NAME') ?: 'the day care center that uses LittleStep',
        'contact_email' => env('PRIVACY_CONTACT_EMAIL') ?: null,
        'contact_phone' => env('PRIVACY_CONTACT_PHONE') ?: null,
        'updated' => env('PRIVACY_POLICY_UPDATED') ?: '2026-10-20',
    ],
];
