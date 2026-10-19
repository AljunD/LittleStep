<?php

return [
    'guardian_default_password' => env('GUARDIAN_DEFAULT_PASSWORD', 'Password123@'),

    'guardian_app_login_url' => env('GUARDIAN_APP_LOGIN_URL', 'littlestep://login'),

    // Days to keep System Logs entries before the daily prune removes them. Empty keeps them forever.
    'audit_log_retention_days' => env('AUDIT_LOG_RETENTION_DAYS') ?: null,
];
