<?php

namespace App\DTOs;

class GateUserInfoDTO
{
    /**
     * @param  list<string>  $appRoles
     * @param  array<string, mixed>  $organizationAttributes
     */
    public function __construct(
        public string $gateUserId,
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $nik = null,
        public ?string $nisNip = null,
        public string $userType = 'santri',
        public ?string $gender = null,
        public string $sourceStatus = 'active',
        public array $appRoles = [],
        public array $organizationAttributes = [],
        public ?string $checksum = null,
        public ?string $sourceUpdatedAt = null,
        public ?string $sourceVersion = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $appRoles = [];
        if (isset($data['roles']) && is_array($data['roles'])) {
            $appRoles = array_values(array_map('strval', $data['roles']));
        } elseif (isset($data['app_roles']) && is_array($data['app_roles'])) {
            $appRoles = array_values(array_map('strval', $data['app_roles']));
        }

        $orgAttributes = [];
        if (isset($data['organization_attributes']) && is_array($data['organization_attributes'])) {
            $orgAttributes = $data['organization_attributes'];
        } elseif (isset($data['org']) && is_array($data['org'])) {
            $orgAttributes = $data['org'];
        }

        return new self(
            gateUserId: (string) ($data['gate_user_id'] ?? $data['sub'] ?? $data['id'] ?? ''),
            name: (string) ($data['name'] ?? $data['preferred_username'] ?? ''),
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : (isset($data['phone_number']) ? (string) $data['phone_number'] : null),
            nik: isset($data['nik']) ? (string) $data['nik'] : null,
            nisNip: isset($data['nis_nip']) ? (string) $data['nis_nip'] : (isset($data['nis']) ? (string) $data['nis'] : (isset($data['nip']) ? (string) $data['nip'] : null)),
            userType: (string) ($data['user_type'] ?? $data['type'] ?? 'santri'),
            gender: isset($data['gender']) ? (string) $data['gender'] : null,
            sourceStatus: (string) ($data['source_status'] ?? $data['status'] ?? 'active'),
            appRoles: $appRoles,
            organizationAttributes: $orgAttributes,
            checksum: isset($data['checksum']) ? (string) $data['checksum'] : null,
            sourceUpdatedAt: isset($data['source_updated_at']) ? (string) $data['source_updated_at'] : null,
            sourceVersion: isset($data['source_version']) ? (string) $data['source_version'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gate_user_id' => $this->gateUserId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'nik' => $this->nik,
            'nis_nip' => $this->nisNip,
            'user_type' => $this->userType,
            'gender' => $this->gender,
            'source_status' => $this->sourceStatus,
            'app_roles' => $this->appRoles,
            'organization_attributes' => $this->organizationAttributes,
            'checksum' => $this->checksum,
            'source_updated_at' => $this->sourceUpdatedAt,
            'source_version' => $this->sourceVersion,
        ];
    }
}
