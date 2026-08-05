<?php

namespace App\DTOs;

class GateUserDTO
{
    public function __construct(
        public string $gateUserId,
        public string $name,
        public ?string $nik = null,
        public ?string $nisNip = null,
        public string $userType = 'santri',
        public ?string $gender = null,
        public ?string $phone = null,
        public ?string $email = null,
        public string $sourceStatus = 'active',
        public ?string $sourceUpdatedAt = null,
        public ?string $sourceVersion = null,
        public ?string $checksum = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            gateUserId: (string) ($data['gate_user_id'] ?? $data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            nik: isset($data['nik']) ? (string) $data['nik'] : null,
            nisNip: isset($data['nis_nip']) ? (string) $data['nis_nip'] : null,
            userType: (string) ($data['user_type'] ?? 'santri'),
            gender: isset($data['gender']) ? (string) $data['gender'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            sourceStatus: (string) ($data['source_status'] ?? $data['status'] ?? 'active'),
            sourceUpdatedAt: isset($data['source_updated_at']) ? (string) $data['source_updated_at'] : null,
            sourceVersion: isset($data['source_version']) ? (string) $data['source_version'] : null,
            checksum: isset($data['checksum']) ? (string) $data['checksum'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'gate_user_id' => $this->gateUserId,
            'name' => $this->name,
            'nik' => $this->nik,
            'nis_nip' => $this->nisNip,
            'user_type' => $this->userType,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'email' => $this->email,
            'source_status' => $this->sourceStatus,
            'source_updated_at' => $this->sourceUpdatedAt,
            'source_version' => $this->sourceVersion,
            'checksum' => $this->checksum,
        ];
    }
}
