<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SABIRA POSKESTREN Outbound Integrations Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for downstream system integrations such as SABIRA Absensi,
    | Dormitory Management, and Guardians. By default, external transports are
    | disabled and run using the in-memory 'fake' driver.
    |
    */

    'attendance' => [
        'enabled' => (bool) env('ATTENDANCE_INTEGRATION_ENABLED', false),
        'driver' => env('ATTENDANCE_INTEGRATION_DRIVER', 'fake'), // fake, sandbox, http
        'max_retry_attempts' => (int) env('ATTENDANCE_INTEGRATION_MAX_RETRIES', 5),
        'retry_backoff_seconds' => (int) env('ATTENDANCE_INTEGRATION_BACKOFF_SECONDS', 60),
        'endpoint_url' => env('ATTENDANCE_INTEGRATION_ENDPOINT_URL', 'https://absensi-sandbox.sabira.id/api/v1/health-dispositions'),
        'api_key' => env('ATTENDANCE_INTEGRATION_API_KEY', null),
        'timeout_seconds' => (int) env('ATTENDANCE_INTEGRATION_TIMEOUT', 5),
    ],

    'outbox' => [
        'batch_size' => (int) env('INTEGRATION_OUTBOX_BATCH_SIZE', 25),
        'max_attempts' => (int) env('INTEGRATION_OUTBOX_MAX_ATTEMPTS', 5),
    ],
];
