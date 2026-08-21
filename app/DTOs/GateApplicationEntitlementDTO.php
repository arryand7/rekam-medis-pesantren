<?php

namespace App\DTOs;

class GateApplicationEntitlementDTO
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public string $gateUserId,
        public string $appCode,
        public string $status, // 'allowed', 'revoked', 'suspended', 'not_assigned'
        public ?string $assignedAt = null,
        public ?string $expiresAt = null,
        public array $roles = []
    ) {}

    public function isAllowed(): bool
    {
        // Gate SSO menggunakan status 'active' untuk akses yang diberikan.
        // 'allowed' adalah alias legacy yang tetap didukung.
        return in_array(strtolower($this->status), ['active', 'allowed'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $roles = [];
        if (isset($data['roles']) && is_array($data['roles'])) {
            $roles = array_values(array_map('strval', $data['roles']));
        }

        return new self(
            gateUserId: (string) ($data['gate_user_id'] ?? $data['user_id'] ?? ''),
            appCode: (string) ($data['app_code'] ?? $data['application_code'] ?? $data['slug'] ?? 'unknown'),
            status: (string) ($data['status'] ?? 'not_assigned'),
            assignedAt: isset($data['assigned_at']) ? (string) $data['assigned_at'] : (isset($data['granted_at']) ? (string) $data['granted_at'] : null),
            expiresAt: isset($data['expires_at']) ? (string) $data['expires_at'] : null,
            roles: $roles
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gate_user_id' => $this->gateUserId,
            'app_code' => $this->appCode,
            'status' => $this->status,
            'assigned_at' => $this->assignedAt,
            'expires_at' => $this->expiresAt,
            'roles' => $this->roles,
        ];
    }
}
