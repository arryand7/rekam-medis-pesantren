<?php

namespace App\DTOs;

class GateOidcTokenResponseDTO
{
    public function __construct(
        public string $accessToken,
        public ?string $idToken = null,
        public string $tokenType = 'Bearer',
        public int $expiresIn = 3600,
        public ?string $refreshToken = null,
        public ?string $scope = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            idToken: isset($data['id_token']) ? (string) $data['id_token'] : null,
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
            expiresIn: (int) ($data['expires_in'] ?? 3600),
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            scope: isset($data['scope']) ? (string) $data['scope'] : null
        );
    }
}
