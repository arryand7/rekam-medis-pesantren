<?php

namespace App\Services\Gate;

use App\Contracts\GateOidcClientContract;
use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateOidcTokenResponseDTO;
use App\DTOs\GateUserInfoDTO;
use App\Services\SsoConfigurationService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class HttpGateOidcClient implements GateOidcClientContract
{
    public function __construct(
        private readonly SsoConfigurationService $configuration
    ) {}

    public function getAuthorizationUrl(string $state, ?string $nonce = null, ?string $codeChallenge = null): string
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.authorize', '/oauth/authorize');
        $clientId = (string) $settings['client_id'];
        $redirectUri = urlencode((string) $settings['redirect_uri']);
        $scopes = urlencode((string) $settings['scopes']);

        $url = "{$baseUrl}{$endpoint}?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scopes}&state={$state}";
        if ($nonce) {
            $url .= "&nonce={$nonce}";
        }
        if ($codeChallenge) {
            $url .= "&code_challenge={$codeChallenge}&code_challenge_method=S256";
        }

        return $url;
    }

    public function exchangeAuthorizationCode(string $code, ?string $codeVerifier = null): GateOidcTokenResponseDTO
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.token', '/oauth/token');
        $timeout = (int) $settings['http_timeout'];

        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'redirect_uri' => $settings['redirect_uri'],
            'code' => $code,
        ];

        if ($codeVerifier) {
            $params['code_verifier'] = $codeVerifier;
        }

        try {
            $response = Http::timeout($timeout)
                ->asForm()
                ->post("{$baseUrl}{$endpoint}", $params);

            if (! $response->successful()) {
                Log::warning('Gate token exchange failed', [
                    'status' => $response->status(),
                    'error' => $response->json('error'),
                ]);
                throw new RuntimeException('Pertukaran kode otorisasi Gate gagal dengan status HTTP '.$response->status());
            }

            $data = $response->json();

            return GateOidcTokenResponseDTO::fromArray($data);
        } catch (Throwable $e) {
            Log::error('Gate token exchange exception', ['exception_class' => $e::class]);
            throw new RuntimeException('Tidak dapat menghubungi server Gate.', 0, $e);
        }
    }

    public function fetchUserInfo(string $accessToken): GateUserInfoDTO
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.userinfo', '/oauth/userinfo');
        try {
            $response = $this->request($settings)
                ->withToken($accessToken)
                ->get("{$baseUrl}{$endpoint}");

            if (! $response->successful()) {
                throw new RuntimeException('Gagal mengambil data UserInfo dari Gate: HTTP '.$response->status());
            }

            $data = $response->json();

            return GateUserInfoDTO::fromArray($data);
        } catch (Throwable $e) {
            Log::error('Gate userinfo fetch exception', ['exception_class' => $e::class]);
            throw new RuntimeException('Gagal memproses UserInfo Gate.', 0, $e);
        }
    }

    public function fetchApplicationEntitlement(string $accessToken, string $gateUserId, string $appCode): GateApplicationEntitlementDTO
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.entitlements', '/api/v1/entitlements');
        try {
            $response = $this->request($settings)
                ->withToken($accessToken)
                ->get("{$baseUrl}{$endpoint}/{$gateUserId}/{$appCode}");

            if ($response->status() === 404 || $response->status() === 403) {
                return new GateApplicationEntitlementDTO(
                    gateUserId: $gateUserId,
                    appCode: $appCode,
                    status: 'not_assigned'
                );
            }

            if (! $response->successful()) {
                throw new RuntimeException('Gagal memeriksa entitlement aplikasi Gate: HTTP '.$response->status());
            }

            return GateApplicationEntitlementDTO::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Gate entitlement check exception', ['exception_class' => $e::class]);

            return new GateApplicationEntitlementDTO(
                gateUserId: $gateUserId,
                appCode: $appCode,
                status: 'not_assigned'
            );
        }
    }

    public function getEndSessionUrl(?string $idToken = null, ?string $postLogoutRedirectUri = null): ?string
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.end_session', '/oauth/logout');

        $url = "{$baseUrl}{$endpoint}";
        if ($postLogoutRedirectUri) {
            $url .= '?post_logout_redirect_uri='.urlencode($postLogoutRedirectUri);
        }

        return $url;
    }

    public function probeHealth(): array
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.health', '/health');
        try {
            $response = $this->request($settings, 3)->get("{$baseUrl}{$endpoint}");

            return [
                'driver' => 'http',
                'enabled' => (bool) $settings['sso_enabled'],
                'reachable' => $response->successful(),
                'message' => $response->successful() ? 'Gate OIDC Server online.' : 'Gate Server HTTP '.$response->status(),
            ];
        } catch (Throwable) {
            return [
                'driver' => 'http',
                'enabled' => (bool) $settings['sso_enabled'],
                'reachable' => false,
                'message' => 'Gate Server unreachable.',
            ];
        }
    }

    /** @param array<string, mixed> $settings */
    private function request(array $settings, ?int $timeout = null): PendingRequest
    {
        $request = Http::timeout($timeout ?? (int) $settings['http_timeout']);
        $attempts = (int) $settings['retry_attempts'];

        return $attempts > 0
            ? $request->retry($attempts, (int) $settings['retry_backoff_ms'], throw: false)
            : $request;
    }
}
