<?php

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\Gate\GateAuthenticationService;

test('identity projection creates Person, User, and Patient records atomically', function () {
    $service = app(GateAuthenticationService::class);

    $userInfo = new GateUserInfoDTO(
        gateUserId: 'GATE-PROJ-001',
        name: 'Santri Zaid',
        email: 'zaid@sabira.id',
        nik: '3201010101010001',
        nisNip: 'NIS-2026-001',
        userType: 'santri',
        gender: 'laki-laki',
        sourceStatus: 'active'
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-PROJ-001',
        appCode: 'poskestren-health',
        status: 'allowed'
    );

    $user = $service->projectIdentity($userInfo, $entitlement);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Santri Zaid');

    $person = Person::where('gate_user_id', 'GATE-PROJ-001')->first();
    expect($person)->not->toBeNull();
    expect($person->nis_nip)->toBe('NIS-2026-001');

    $patient = Patient::where('person_id', $person->id)->first();
    expect($patient)->not->toBeNull();
    expect($patient->patient_number)->toStartWith('RM-');
});

test('subsequent identity projections update authoritative fields without duplicate records or touching medical data', function () {
    $service = app(GateAuthenticationService::class);

    $userInfo1 = new GateUserInfoDTO(
        gateUserId: 'GATE-PROJ-002',
        name: 'Ahmad Awal',
        email: 'ahmad@sabira.id',
        userType: 'santri',
        sourceStatus: 'active'
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-PROJ-002',
        appCode: 'poskestren-health',
        status: 'allowed'
    );

    $service->projectIdentity($userInfo1, $entitlement);

    $initialPersonCount = Person::where('gate_user_id', 'GATE-PROJ-002')->count();
    expect($initialPersonCount)->toBe(1);

    // Update from Gate
    $userInfo2 = new GateUserInfoDTO(
        gateUserId: 'GATE-PROJ-002',
        name: 'Ahmad Baru',
        email: 'ahmad.baru@sabira.id',
        phone: '081299998888',
        userType: 'santri',
        sourceStatus: 'active'
    );

    $service->projectIdentity($userInfo2, $entitlement);

    $finalPersonCount = Person::where('gate_user_id', 'GATE-PROJ-002')->count();
    expect($finalPersonCount)->toBe(1);

    $person = Person::where('gate_user_id', 'GATE-PROJ-002')->first();
    expect($person->name)->toBe('Ahmad Baru');
    expect($person->phone)->toBe('081299998888');
});

test('technical accounts are not eligible for patient record creation', function () {
    $service = app(GateAuthenticationService::class);

    $userInfo = new GateUserInfoDTO(
        gateUserId: 'GATE-BOT-001',
        name: 'Backup Service Account',
        email: 'backup@sabira.id',
        userType: 'service_account',
        sourceStatus: 'active'
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-BOT-001',
        appCode: 'poskestren-health',
        status: 'allowed'
    );

    $service->projectIdentity($userInfo, $entitlement);

    $person = Person::where('gate_user_id', 'GATE-BOT-001')->first();
    expect($person)->not->toBeNull();
    expect($person->isHumanPatientEligible())->toBeFalse();

    $patient = Patient::where('person_id', $person->id)->first();
    expect($patient)->toBeNull();
});
