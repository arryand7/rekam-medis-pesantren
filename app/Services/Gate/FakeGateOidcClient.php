<?php

namespace App\Services\Gate;

use App\Contracts\GateOidcClientContract;
use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateOidcTokenResponseDTO;
use App\DTOs\GateUserInfoDTO;
use RuntimeException;

class FakeGateOidcClient implements GateOidcClientContract
{
    /**
     * @var array<string, GateUserInfoDTO>
     */
    protected static array $mockUsers = [];

    /**
     * @var array<string, GateApplicationEntitlementDTO>
     */
    protected static array $mockEntitlements = [];

    protected static bool $shouldFailTokenExchange = false;

    protected static bool $isReachable = true;

    public static function reset(): void
    {
        self::$mockUsers = [];
        self::$mockEntitlements = [];
        self::$shouldFailTokenExchange = false;
        self::$isReachable = true;
    }

    public static function setFailTokenExchange(bool $fail): void
    {
        self::$shouldFailTokenExchange = $fail;
    }

    public static function setReachable(bool $reachable): void
    {
        self::$isReachable = $reachable;
    }

    public static function addMockUser(GateUserInfoDTO $user, ?GateApplicationEntitlementDTO $entitlement = null): void
    {
        self::$mockUsers[$user->gateUserId] = $user;
        if ($entitlement) {
            self::$mockEntitlements[$user->gateUserId] = $entitlement;
        } else {
            self::$mockEntitlements[$user->gateUserId] = new GateApplicationEntitlementDTO(
                gateUserId: $user->gateUserId,
                appCode: config('gate.app_code', 'poskestren-health'),
                status: 'allowed',
                roles: $user->appRoles
            );
        }
    }

    public function getAuthorizationUrl(string $state, ?string $nonce = null, ?string $codeChallenge = null): string
    {
        $baseUrl = config('gate.base_url', 'https://gate.sabira.id');
        $clientId = config('gate.client_id', 'poskestren-health-app');
        $redirectUri = urlencode(config('gate.redirect_uri', 'http://localhost:8000/auth/gate/callback'));
        $scopes = urlencode(config('gate.scopes', 'openid profile email'));

        $url = "{$baseUrl}/oauth/authorize?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scopes}&state={$state}";
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
        if (self::$shouldFailTokenExchange || $code === 'invalid_code') {
            throw new RuntimeException('Gate token exchange failed: invalid authorization code.');
        }

        return new GateOidcTokenResponseDTO(
            accessToken: 'fake_access_token_'.md5($code),
            idToken: 'fake_id_token_'.md5($code),
            tokenType: 'Bearer',
            expiresIn: 3600,
            refreshToken: 'fake_refresh_token_'.md5($code),
            scope: config('gate.scopes', 'openid profile email')
        );
    }

    public function fetchUserInfo(string $accessToken): GateUserInfoDTO
    {
        // Extract user from token if registered in mock
        foreach (self::$mockUsers as $user) {
            return $user;
        }

        // Default canned user
        return new GateUserInfoDTO(
            gateUserId: 'GATE-USR-DEFAULT-001',
            name: 'dr. H. Abdullah Mansur',
            email: 'abdullah.mansur@sabira.id',
            phone: '081234567890',
            nik: '3201234567890001',
            nisNip: 'NIP-19800101',
            userType: 'tenaga_kesehatan',
            gender: 'laki-laki',
            sourceStatus: 'active',
            appRoles: ['health_officer'],
            checksum: 'CHECKSUM-DEF-001'
        );
    }

    public function fetchApplicationEntitlement(string $accessToken, string $gateUserId, string $appCode): GateApplicationEntitlementDTO
    {
        if (isset(self::$mockEntitlements[$gateUserId])) {
            return self::$mockEntitlements[$gateUserId];
        }

        if (str_contains($gateUserId, 'DENIED') || str_contains($gateUserId, 'NOT_ASSIGNED')) {
            return new GateApplicationEntitlementDTO(
                gateUserId: $gateUserId,
                appCode: $appCode,
                status: 'not_assigned'
            );
        }

        if (str_contains($gateUserId, 'REVOKED')) {
            return new GateApplicationEntitlementDTO(
                gateUserId: $gateUserId,
                appCode: $appCode,
                status: 'revoked'
            );
        }

        return new GateApplicationEntitlementDTO(
            gateUserId: $gateUserId,
            appCode: $appCode,
            status: 'allowed',
            roles: ['health_officer']
        );
    }

    public function getEndSessionUrl(?string $idToken = null, ?string $postLogoutRedirectUri = null): ?string
    {
        $baseUrl = config('gate.base_url', 'https://gate.sabira.id');
        $endpoint = config('gate.endpoints.end_session', '/oauth/logout');

        $url = "{$baseUrl}{$endpoint}";
        if ($postLogoutRedirectUri) {
            $url .= '?post_logout_redirect_uri='.urlencode($postLogoutRedirectUri);
        }

        return $url;
    }

    public function probeHealth(): array
    {
        return [
            'driver' => 'fake',
            'enabled' => config('gate.sso_enabled', false),
            'reachable' => self::$isReachable,
            'message' => self::$isReachable ? 'Fake Gate OIDC Client ready.' : 'Fake Gate service unreachable.',
        ];
    }
}
