<?php

namespace App\Http\Requests\Admin;

use App\Services\SsoConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSsoConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $values = [
            'sso_enabled' => $this->boolean('sso_enabled'),
        ];

        foreach (['driver', 'base_url', 'client_id', 'redirect_uri', 'scopes', 'app_code'] as $field) {
            if ($this->has($field)) {
                $values[$field] = trim((string) $this->input($field));
            }
        }

        $this->merge($values);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'sso_enabled' => ['required', 'boolean'],
            'driver' => ['required', 'string', 'in:fake,http'],
            'base_url' => ['required', 'url', 'max:500'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'min:16', 'max:4096'],
            'redirect_uri' => ['required', 'url', 'max:500'],
            'scopes' => ['required', 'string', 'max:500', 'regex:/^[a-zA-Z0-9:._-]+(?:\s+[a-zA-Z0-9:._-]+)*$/'],
            'app_code' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'http_timeout' => ['required', 'integer', 'min:2', 'max:30'],
            'retry_attempts' => ['required', 'integer', 'min:0', 'max:5'],
            'retry_backoff_ms' => ['required', 'integer', 'min:0', 'max:5000'],
            'entitlement_ttl_seconds' => ['required', 'integer', 'min:60', 'max:3600'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $baseUrl = (string) $this->input('base_url');
            $redirectUri = (string) $this->input('redirect_uri');
            $driver = (string) $this->input('driver');

            if ($this->containsUrlCredentials($baseUrl)) {
                $validator->errors()->add('base_url', 'URL provider tidak boleh memuat username atau password.');
            }

            if (parse_url($baseUrl, PHP_URL_QUERY) !== null || parse_url($baseUrl, PHP_URL_FRAGMENT) !== null) {
                $validator->errors()->add('base_url', 'URL provider tidak boleh memuat query string atau fragment.');
            }

            if ($driver === 'http' && ! $this->usesSecureTransport($baseUrl)) {
                $validator->errors()->add('base_url', 'Mode HTTP Gate wajib menggunakan HTTPS, kecuali localhost pada environment lokal.');
            }

            if (! $this->usesSecureTransport($redirectUri)) {
                $validator->errors()->add('redirect_uri', 'Callback wajib menggunakan HTTPS, kecuali localhost pada environment lokal.');
            }

            if (parse_url($redirectUri, PHP_URL_PATH) !== '/auth/gate/callback') {
                $validator->errors()->add('redirect_uri', 'Path callback harus tepat /auth/gate/callback.');
            }

            if (parse_url($redirectUri, PHP_URL_QUERY) !== null || parse_url($redirectUri, PHP_URL_FRAGMENT) !== null) {
                $validator->errors()->add('redirect_uri', 'Callback tidak boleh memuat query string atau fragment.');
            }

            $scopes = preg_split('/\s+/', (string) $this->input('scopes')) ?: [];
            if (! in_array('openid', $scopes, true)) {
                $validator->errors()->add('scopes', 'Scope openid wajib tersedia untuk alur OIDC.');
            }

            if ($driver === 'http') {
                if ($baseUrl === 'https://gate.example.invalid') {
                    $validator->errors()->add('base_url', 'Ganti endpoint contoh dengan URL Gate yang sebenarnya sebelum memakai mode HTTP.');
                }

                if (! filled($this->input('client_id'))) {
                    $validator->errors()->add('client_id', 'Client ID wajib diisi untuk mode HTTP.');
                }

                $secretConfigured = app(SsoConfigurationService::class)->forForm()['client_secret_configured'] ?? false;
                if (! filled($this->input('client_secret')) && ! $secretConfigured) {
                    $validator->errors()->add('client_secret', 'Client secret wajib diisi untuk mode HTTP.');
                }
            }

            if (! $this->boolean('sso_enabled')) {
                return;
            }

            if ($driver !== 'http') {
                $validator->errors()->add('driver', 'SSO hanya dapat diaktifkan pada mode Server Gate (HTTP).');
            }
        }];
    }

    private function containsUrlCredentials(string $url): bool
    {
        return parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_PASS) !== null;
    }

    private function usesSecureTransport(string $url): bool
    {
        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https') {
            return true;
        }

        if (! app()->environment(['local', 'testing'])) {
            return false;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), ['localhost', '127.0.0.1', '::1'], true);
    }
}
