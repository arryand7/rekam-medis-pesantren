<?php

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;
use App\Services\Gate\FakeGateOidcClient;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    FakeGateOidcClient::reset();
});

test('user with allowed entitlement can log in successfully', function () {
    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-ALLOWED-001',
        name: 'dr. Sarah Humaira',
        email: 'sarah@sabira.id',
        userType: 'tenaga_kesehatan',
        sourceStatus: 'active',
        appRoles: ['health_officer']
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-USR-ALLOWED-001',
        appCode: 'poskestren-health',
        status: 'allowed',
        roles: ['health_officer']
    );

    FakeGateOidcClient::addMockUser($userDTO, $entitlement);
    session(['gate_auth_state' => 'valid_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=valid_state');

    $response->assertRedirect(route('dashboard'));
    expect(Auth::check())->toBeTrue();
});

test('user with not_assigned or revoked entitlement is redirected to access denied page', function () {
    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-REVOKED-001',
        name: 'Fulan bin Fulan',
        email: 'fulan@sabira.id',
        userType: 'santri',
        sourceStatus: 'active'
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-USR-REVOKED-001',
        appCode: 'poskestren-health',
        status: 'revoked'
    );

    FakeGateOidcClient::addMockUser($userDTO, $entitlement);
    session(['gate_auth_state' => 'valid_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=valid_state');

    $response->assertRedirect('/auth/gate/access-denied');
    expect(Auth::check())->toBeFalse();
});

test('Gate role admin does not automatically grant clinical medical record permissions', function () {
    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-ADMIN-001',
        name: 'Admin Sekolah',
        email: 'admin.sekolah@sabira.id',
        userType: 'staf',
        sourceStatus: 'active',
        appRoles: ['school_admin'] // maps to administrator
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-USR-ADMIN-001',
        appCode: 'poskestren-health',
        status: 'allowed',
        roles: ['school_admin']
    );

    FakeGateOidcClient::addMockUser($userDTO, $entitlement);
    session(['gate_auth_state' => 'valid_state']);

    $this->get('/auth/gate/callback?code=valid_code&state=valid_state');

    $user = Auth::user();
    expect($user)->not->toBeNull();
    // Verify clinical permissions are not granted by default
    expect($user->hasPermission('view-clinical-dashboard'))->toBeFalse();
});
