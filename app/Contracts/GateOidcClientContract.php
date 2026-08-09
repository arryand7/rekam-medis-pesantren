<?php

namespace App\Contracts;

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateOidcTokenResponseDTO;
use App\DTOs\GateUserInfoDTO;

interface GateOidcClientContract
{
    /**
     * Generate the authorization URL for Gate SSO redirect.
     */
    public function getAuthorizationUrl(string $state, ?string $nonce = null, ?string $codeChallenge = null): string;

    /**
     * Exchange an authorization code for access and ID tokens.
     */
    public function exchangeAuthorizationCode(string $code, ?string $codeVerifier = null): GateOidcTokenResponseDTO;

    /**
     * Fetch user claims from Gate UserInfo endpoint.
     */
    public function fetchUserInfo(string $accessToken): GateUserInfoDTO;

    /**
     * Fetch application entitlement status for a specific user.
     */
    public function fetchApplicationEntitlement(string $accessToken, string $gateUserId, string $appCode): GateApplicationEntitlementDTO;

    /**
     * Generate end session / logout URL if supported by Gate.
     */
    public function getEndSessionUrl(?string $idToken = null, ?string $postLogoutRedirectUri = null): ?string;

    /**
     * Probe Gate SSO service connectivity and health.
     *
     * @return array{driver: string, enabled: bool, reachable: bool, message: string}
     */
    public function probeHealth(): array;
}
