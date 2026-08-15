<?php

/** @return array<string, mixed> */
function ssoConfigurationPayload(array $overrides = []): array
{
    return array_merge([
        'sso_enabled' => true,
        'driver' => 'http',
        'base_url' => 'https://gate.sabira.test',
        'client_id' => 'poskestren-health-test',
        'client_secret' => 'synthetic-sso-secret-value-123456',
        'redirect_uri' => 'https://health.sabira.test/auth/gate/callback',
        'scopes' => 'openid profile email poskestren_access',
        'app_code' => 'poskestren-health',
        'http_timeout' => 5,
        'retry_attempts' => 2,
        'retry_backoff_ms' => 200,
        'entitlement_ttl_seconds' => 300,
    ], $overrides);
}
