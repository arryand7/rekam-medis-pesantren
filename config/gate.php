<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SABIRA Gate Identity & OIDC Configuration
    |--------------------------------------------------------------------------
    */
    'settings_cache_key' => 'gate_sso_configuration.current',
    'base_url' => 'https://gate.example.invalid',
    'client_id' => '',
    'client_secret' => '',
    'redirect_uri' => rtrim((string) config('app.url', 'http://localhost:8000'), '/').'/auth/gate/callback',
    'scopes' => 'openid profile email phone offline_access poskestren_access',
    'app_code' => 'poskestren-health',

    /*
    |--------------------------------------------------------------------------
    | Gate Feature Flags
    |--------------------------------------------------------------------------
    */
    'sso_enabled' => false,
    'sync_apply_enabled' => env('GATE_SYNC_APPLY_ENABLED', false),
    'webhook_enabled' => env('GATE_WEBHOOK_ENABLED', false),
    'driver' => 'fake', // Persistent Super Admin setting: 'http' or safe 'fake' fallback.

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
        'users' => '/api/provisioning/users',
        'provisioning_me' => '/api/provisioning/me',
        'sync_results' => '/api/provisioning/sync-results',
        'end_session' => '/oauth/logout',
        'health' => '/api/provisioning/me', // Gate SSO tidak punya /health; gunakan /me sebagai health check
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => 5,
        'retry_attempts' => 2,
        'retry_backoff_ms' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session & Entitlement Revalidation
    |--------------------------------------------------------------------------
    */
    'entitlement_revalidation_ttl_seconds' => 300, // 5 minutes

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
        'username' => env('BREAK_GLASS_USERNAME', ''),
        'alert_email' => env('BREAK_GLASS_ALERT_EMAIL', ''),
    ],
];
