<?php

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;

test('GateApplicationEntitlementDTO correctly recognizes active and allowed status', function () {
    $allowed = new GateApplicationEntitlementDTO(
        gateUserId: 'USR-001',
        appCode: 'poskestren-rekam-medis',
        status: 'allowed'
    );
    expect($allowed->isAllowed())->toBeTrue();

    $active = new GateApplicationEntitlementDTO(
        gateUserId: 'USR-001',
        appCode: 'poskestren-rekam-medis',
        status: 'active'
    );
    expect($active->isAllowed())->toBeTrue();

    $notAssigned = new GateApplicationEntitlementDTO(
        gateUserId: 'USR-001',
        appCode: 'poskestren-rekam-medis',
        status: 'not_assigned'
    );
    expect($notAssigned->isAllowed())->toBeFalse();

    $revoked = new GateApplicationEntitlementDTO(
        gateUserId: 'USR-001',
        appCode: 'poskestren-rekam-medis',
        status: 'revoked'
    );
    expect($revoked->isAllowed())->toBeFalse();
});

test('GateUserInfoDTO parses application_access claim from Gate userinfo', function () {
    $rawPayload = [
        'sub' => 'GATE-123',
        'name' => 'Ahmad Santri',
        'email' => 'ahmad@example.com',
        'type' => 'santri',
        'application_access' => [
            'app_code' => 'poskestren-rekam-medis',
            'application_name' => 'POSKESTREN Rekam Medis',
            'status' => 'active',
            'application_role' => 'user',
            'granted_at' => '2026-08-21T10:00:00Z',
        ],
    ];

    $dto = GateUserInfoDTO::fromArray($rawPayload);

    expect($dto->gateUserId)->toBe('GATE-123')
        ->and($dto->name)->toBe('Ahmad Santri')
        ->and($dto->applicationAccess)->toBeArray()
        ->and($dto->applicationAccess['status'])->toBe('active')
        ->and($dto->applicationAccess['app_code'])->toBe('poskestren-rekam-medis');

    $entitlement = GateApplicationEntitlementDTO::fromArray(array_merge(
        $dto->applicationAccess,
        ['gate_user_id' => $dto->gateUserId]
    ));

    expect($entitlement->isAllowed())->toBeTrue()
        ->and($entitlement->appCode)->toBe('poskestren-rekam-medis')
        ->and($entitlement->status)->toBe('active');
});
