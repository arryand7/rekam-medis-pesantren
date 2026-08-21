<?php

use App\DTOs\GateUserDTO;

describe('GateUserDTO', function () {
    it('correctly parses photo fields from Gate provisioning API format', function () {
        $data = [
            'uuid' => 'some-uuid-1234',
            'gate_user_uuid' => 'some-uuid-1234',
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@sabira-iibs.id',
            'type' => 'admin',
            'user_type' => 'admin',
            'status' => 'active',
            'updated_at' => '2026-08-01T10:00:00+00:00',
            'photo' => [
                'available' => true,
                'url' => 'http://localhost:8001/api/provisioning/photo/2?expires=123&signature=abc',
                'checksum' => '34e6ad35d541cc8e1b80c9053c79cf1cb1ec82ba3fac6aabc85ec5764f90703c',
            ],
        ];

        $dto = GateUserDTO::fromArray($data);

        expect($dto->gateUserId)->toBe('some-uuid-1234')
            ->and($dto->name)->toBe('Administrator')
            ->and($dto->userType)->toBe('admin')
            ->and($dto->sourceStatus)->toBe('active')
            ->and($dto->photoAvailable)->toBeTrue()
            ->and($dto->photoUrl)->toContain('http://localhost:8001')
            ->and($dto->photoChecksum)->toBe('34e6ad35d541cc8e1b80c9053c79cf1cb1ec82ba3fac6aabc85ec5764f90703c');
    });

    it('handles missing photo gracefully', function () {
        $data = [
            'uuid' => 'some-uuid-5678',
            'name' => 'Super Administrator',
            'status' => 'active',
            'photo' => [
                'available' => false,
                'url' => null,
                'checksum' => null,
            ],
        ];

        $dto = GateUserDTO::fromArray($data);

        expect($dto->photoAvailable)->toBeFalse()
            ->and($dto->photoUrl)->toBeNull()
            ->and($dto->photoChecksum)->toBeNull();
    });

    it('handles absent photo key gracefully', function () {
        $data = [
            'gate_user_id' => 'legacy-id-001',
            'name' => 'Legacy User',
            'status' => 'active',
        ];

        $dto = GateUserDTO::fromArray($data);

        expect($dto->photoAvailable)->toBeFalse()
            ->and($dto->photoUrl)->toBeNull()
            ->and($dto->photoChecksum)->toBeNull();
    });

    it('parses NIS from gate api response', function () {
        $data = [
            'uuid' => 'santri-uuid',
            'name' => 'Ahmad Santri',
            'status' => 'active',
            'nis' => 'SAN-2026-001',
            'nip' => null,
        ];

        $dto = GateUserDTO::fromArray($data);

        expect($dto->nisNip)->toBe('SAN-2026-001');
    });

    it('serialises back to array with photo sub-key', function () {
        $dto = new GateUserDTO(
            gateUserId: 'test-id',
            name: 'Test User',
            photoAvailable: true,
            photoUrl: 'http://example.com/photo',
            photoChecksum: 'abc123',
        );

        $arr = $dto->toArray();

        expect($arr)->toHaveKey('photo')
            ->and($arr['photo']['available'])->toBeTrue()
            ->and($arr['photo']['url'])->toBe('http://example.com/photo')
            ->and($arr['photo']['checksum'])->toBe('abc123');
    });
});
