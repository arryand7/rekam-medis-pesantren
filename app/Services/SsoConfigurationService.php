<?php

namespace App\Services;

use App\Models\SsoConfiguration;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SsoConfigurationService
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        $source = $this->sourceValues();
        $persisted = $this->persistedPayload();

        if ($persisted === null || ! $persisted['exists']) {
            return array_merge($source, [
                'client_secret_configured' => filled($source['client_secret']),
                'is_customized' => false,
                'is_ready' => $this->isReady($source),
                'updated_at' => null,
            ]);
        }

        $secret = $this->decryptSecret($persisted['client_secret_ciphertext']);
        $values = array_merge($source, [
            'sso_enabled' => $persisted['sso_enabled'],
            'driver' => $persisted['driver'],
            'base_url' => $persisted['base_url'],
            'client_id' => $persisted['client_id'],
            'client_secret' => $secret,
            'redirect_uri' => $persisted['redirect_uri'],
            'scopes' => $persisted['scopes'],
            'app_code' => $persisted['app_code'],
            'http_timeout' => $persisted['http_timeout'],
            'retry_attempts' => $persisted['retry_attempts'],
            'retry_backoff_ms' => $persisted['retry_backoff_ms'],
            'entitlement_ttl_seconds' => $persisted['entitlement_ttl_seconds'],
        ]);

        return array_merge($values, [
            'client_secret_configured' => filled($secret),
            'is_customized' => true,
            'is_ready' => $this->isReady($values),
            'updated_at' => $persisted['updated_at'],
        ]);
    }

    /** @return array<string, mixed> */
    public function forForm(): array
    {
        $values = $this->get();
        unset($values['client_secret']);

        return $values;
    }

    /** @param array<string, mixed> $values */
    public function update(array $values): SsoConfiguration
    {
        $current = SsoConfiguration::query()->where('singleton', true)->first();
        $before = $this->safeSnapshot($current);
        $newSecret = trim((string) ($values['client_secret'] ?? ''));
        unset($values['client_secret']);

        $configuration = DB::transaction(function () use ($current, $values, $newSecret, $before): SsoConfiguration {
            $configuration = $current ?? new SsoConfiguration(['singleton' => true]);
            $configuration->fill($values);
            if ($newSecret !== '') {
                $configuration->client_secret = $newSecret;
            }
            $configuration->singleton = true;
            $configuration->save();

            $after = $this->safeSnapshot($configuration);
            $changedFields = array_keys(array_diff_assoc($after, $before));

            AuditLogService::log(
                'SSO_CONFIGURATION_UPDATED',
                SsoConfiguration::class,
                $configuration->id,
                ['values' => $before],
                ['values' => $after, 'changed_fields' => $changedFields],
                'Konfigurasi Gate SSO diperbarui oleh Super Admin.'
            );

            if ($newSecret !== '') {
                AuditLogService::log(
                    'SSO_CLIENT_SECRET_ROTATED',
                    SsoConfiguration::class,
                    $configuration->id,
                    ['client_secret_configured' => $before['client_secret_configured']],
                    ['client_secret_configured' => true],
                    'Client secret Gate SSO disimpan atau dirotasi.'
                );
            }

            return $configuration;
        });

        $this->forget();

        return $configuration;
    }

    public function reset(): void
    {
        $configuration = SsoConfiguration::query()->where('singleton', true)->first();
        $before = $this->safeSnapshot($configuration);

        DB::transaction(function () use ($configuration, $before): void {
            $subjectId = $configuration?->id;
            $configuration?->delete();

            AuditLogService::log(
                'SSO_CONFIGURATION_RESET',
                SsoConfiguration::class,
                $subjectId,
                ['values' => $before],
                ['values' => $this->safeSnapshot(null)],
                'Konfigurasi Gate SSO dikembalikan ke default aman dan nonaktif.'
            );
        });

        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget($this->cacheKey());
    }

    /** @param array<string, mixed> $values */
    private function isReady(array $values): bool
    {
        return $values['driver'] === 'http'
            && filled($values['base_url'])
            && filled($values['client_id'])
            && filled($values['client_secret'])
            && filled($values['redirect_uri'])
            && filled($values['scopes'])
            && filled($values['app_code']);
    }

    /** @return array<string, mixed> */
    private function sourceValues(): array
    {
        return [
            'sso_enabled' => (bool) config('gate.sso_enabled', false),
            'driver' => (string) config('gate.driver', 'fake'),
            'base_url' => (string) config('gate.base_url', 'https://gate.example.invalid'),
            'client_id' => (string) config('gate.client_id', ''),
            'client_secret' => (string) config('gate.client_secret', ''),
            'redirect_uri' => (string) config('gate.redirect_uri', ''),
            'scopes' => (string) config('gate.scopes', 'openid profile email'),
            'app_code' => (string) config('gate.app_code', 'poskestren-health'),
            'http_timeout' => (int) config('gate.http.timeout', 5),
            'retry_attempts' => (int) config('gate.http.retry_attempts', 2),
            'retry_backoff_ms' => (int) config('gate.http.retry_backoff_ms', 200),
            'entitlement_ttl_seconds' => (int) config('gate.entitlement_revalidation_ttl_seconds', 300),
        ];
    }

    /** @return array<string, mixed>|null */
    private function persistedPayload(): ?array
    {
        if (! Schema::hasTable('sso_configurations')) {
            return null;
        }

        return Cache::rememberForever($this->cacheKey(), function (): array {
            $configuration = SsoConfiguration::query()->where('singleton', true)->first();

            if ($configuration === null) {
                return ['exists' => false];
            }

            return [
                'exists' => true,
                'sso_enabled' => $configuration->sso_enabled,
                'driver' => $configuration->driver,
                'base_url' => $configuration->base_url,
                'client_id' => $configuration->client_id,
                'client_secret_ciphertext' => $configuration->getRawOriginal('client_secret'),
                'redirect_uri' => $configuration->redirect_uri,
                'scopes' => $configuration->scopes,
                'app_code' => $configuration->app_code,
                'http_timeout' => $configuration->http_timeout,
                'retry_attempts' => $configuration->retry_attempts,
                'retry_backoff_ms' => $configuration->retry_backoff_ms,
                'entitlement_ttl_seconds' => $configuration->entitlement_ttl_seconds,
                'updated_at' => $configuration->updated_at,
            ];
        });
    }

    private function decryptSecret(mixed $ciphertext): ?string
    {
        if (! is_string($ciphertext) || $ciphertext === '') {
            return null;
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (DecryptException) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function safeSnapshot(?SsoConfiguration $configuration): array
    {
        if ($configuration === null) {
            $source = $this->sourceValues();

            return [
                ...array_diff_key($source, ['client_secret' => true]),
                'client_secret_configured' => filled($source['client_secret']),
            ];
        }

        return [
            ...$configuration->only([
                'sso_enabled',
                'driver',
                'base_url',
                'client_id',
                'redirect_uri',
                'scopes',
                'app_code',
                'http_timeout',
                'retry_attempts',
                'retry_backoff_ms',
                'entitlement_ttl_seconds',
            ]),
            'client_secret_configured' => filled($configuration->client_secret),
        ];
    }

    private function cacheKey(): string
    {
        return (string) config('gate.settings_cache_key', 'gate_sso_configuration.current');
    }
}
