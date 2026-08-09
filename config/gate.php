<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SABIRA Gate Identity & OIDC Configuration
    |--------------------------------------------------------------------------
    */
    'base_url' => env('GATE_BASE_URL', 'https://gate.sabira.id'),
    'client_id' => env('GATE_CLIENT_ID', 'poskestren-health-app'),
    'client_secret' => env('GATE_CLIENT_SECRET', ''),
    'redirect_uri' => env('GATE_REDIRECT_URI', env('APP_URL', 'http://localhost:8000').'/auth/gate/callback'),
    'scopes' => env('GATE_SCOPES', 'openid profile email phone offline_access poskestren_access'),
    'app_code' => env('GATE_APP_CODE', 'poskestren-health'),

    /*
    |--------------------------------------------------------------------------
    | Gate Feature Flags
    |--------------------------------------------------------------------------
    */
    'sso_enabled' => env('GATE_SSO_ENABLED', false),
    'sync_apply_enabled' => env('GATE_SYNC_APPLY_ENABLED', false),
    'webhook_enabled' => env('GATE_WEBHOOK_ENABLED', false),
    'driver' => env('GATE_CLIENT_DRIVER', 'fake'), // 'http' or 'fake'

    /*
    |--------------------------------------------------------------------------
    | Endpoints
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'authorize' => '/oauth/authorize',
        'token' => '/oauth/token',
        'userinfo' => '/oauth/userinfo',
        'entitlements' => '/api/v1/entitlements',
        'users' => '/api/v1/users',
        'end_session' => '/oauth/logout',
        'health' => '/health',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => (int) env('GATE_HTTP_TIMEOUT', 5),
        'retry_attempts' => (int) env('GATE_HTTP_RETRY_ATTEMPTS', 2),
        'retry_backoff_ms' => (int) env('GATE_HTTP_RETRY_BACKOFF_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session & Entitlement Revalidation
    |--------------------------------------------------------------------------
    */
    'entitlement_revalidation_ttl_seconds' => (int) env('GATE_ENTITLEMENT_TTL', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Explicit Role Mapping (Gate Claim -> Local Roles)
    |--------------------------------------------------------------------------
    | Default Deny: Unknown roles are ignored and DO NOT grant permissions.
    | Security Guard: Gate 'admin' DOES NOT automatically grant clinical permissions.
    */
    'role_mapping' => [
        'health_officer' => 'petugas_kesehatan',
        'nurse' => 'perawat',
        'doctor' => 'dokter',
        'pharmacist' => 'farmasi',
        'dorm_supervisor' => 'pembina_asrama',
        'homeroom_teacher' => 'wali_kelas',
        'school_admin' => 'administrator',
    ],

    /*
    |--------------------------------------------------------------------------
    | Break-Glass Local Admin Configuration
    |--------------------------------------------------------------------------
    */
    'break_glass' => [
        'enabled' => env('BREAK_GLASS_ENABLED', false),
        'username' => env('BREAK_GLASS_USERNAME', 'emergency_admin'),
        'alert_email' => env('BREAK_GLASS_ALERT_EMAIL', 'security@sabira.id'),
    ],
];
