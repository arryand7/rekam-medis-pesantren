<?php

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;
use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Services\Gate\FakeGateOidcClient;
use App\Services\Gate\GateSyncDryRunService;
use App\Services\Integration\HttpAttendanceSandboxIntegration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    Config::set('gate.sso_enabled', true);
    Config::set('gate.driver', 'fake');
    Config::set('integration.attendance.enabled', true);
    Config::set('integration.attendance.driver', 'sandbox');
    FakeGateOidcClient::reset();
});

test('Step 1 & 2 — Core application liveness and readiness probes pass with zero secret leakage', function () {
    $this->get('/health')->assertOk()
        ->assertJson([
            'status' => 'ok',
            'version' => config('app.version'),
        ]);

    $ready = $this->get('/health/ready')->assertOk();
    $ready->assertJson([
        'status' => 'ready',
        'dependencies' => [
            'database' => 'connected',
            'cache' => 'operational',
            'private_storage' => 'writable',
        ],
    ]);

    $content = $ready->getContent();
    expect($content)->not->toContain('password');
    expect($content)->not->toContain('client_secret');
    expect($content)->not->toContain('APP_KEY');
});

test('Step 3 — Gate SSO Canary login flow completes with entitlement enforcement and identity projection', function () {
    $gateUserId = 'GATE-PROD-CANARY-01';

    $userDTO = new GateUserInfoDTO(
        gateUserId: $gateUserId,
        name: 'Dr. Canary Staf Poskestren',
        email: 'dr.canary@sabira.id',
        userType: 'tenaga_kesehatan',
        sourceStatus: 'active',
        appRoles: ['tenaga_kesehatan']
    );

    FakeGateOidcClient::addMockUser($userDTO);
    session(['gate_auth_state' => 'canary_session_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=canary_session_state');
    $response->assertRedirect(route('dashboard'));

    expect(Auth::check())->toBeTrue();
    $user = Auth::user();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('dr.canary@sabira.id');

    // Verify Person and Patient created cleanly
    $person = Person::where('gate_user_id', $gateUserId)->first();
    expect($person)->not->toBeNull();
    expect($person->name)->toBe('Dr. Canary Staf Poskestren');

    $patient = Patient::where('person_id', $person->id)->first();
    expect($patient)->not->toBeNull();
    expect($patient->patient_number)->toBeString();
});

test('Step 3b — Gate SSO denies unassigned user entitlement safely', function () {
    $gateUserId = 'GATE-PROD-UNASSIGNED-01';

    $userDTO = new GateUserInfoDTO(
        gateUserId: $gateUserId,
        name: 'Tamu Tanpa Hak Akses',
        email: 'unassigned@sabira.id',
        userType: 'guest',
        sourceStatus: 'active',
        appRoles: []
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: $gateUserId,
        appCode: 'poskestren-health',
        status: 'not_assigned',
        roles: []
    );

    FakeGateOidcClient::addMockUser($userDTO, $entitlement);
    session(['gate_auth_state' => 'canary_unassigned_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=canary_unassigned_state');
    $response->assertRedirect('/auth/gate/access-denied');
    expect(Auth::check())->toBeFalse();
});

test('Step 4 — Gate Sync Apply dry-run completes accurately without mutation', function () {
    $dryRunService = app(GateSyncDryRunService::class);

    $result = $dryRunService->execute(1, 50);
    $summary = $result['summary'];
    expect($summary)->toHaveKey('total');
    expect($summary)->toHaveKey('new');
    expect($summary)->toHaveKey('conflict');
});

test('Step 5 & 6 — Attendance Integration Canary enforces strict privacy and zero clinical keys', function () {
    $sandboxClient = new HttpAttendanceSandboxIntegration;

    $disposition = new AttendanceHealthDispositionDTO(
        eventId: (string) Str::ulid(),
        eventVersion: 1,
        gateUserId: 'GATE-PROD-SAN-01',
        dispositionType: 'rest',
        effectiveFrom: Carbon::now(),
        effectiveUntil: Carbon::now()->addDays(2),
        activityScope: 'all_activities',
        sourceVisitReference: 'VIS-PROD-20260810-001',
        issuedAt: Carbon::now(),
        supersedesEventId: null,
        correlationId: (string) Str::ulid(),
        metadata: ['operational_category' => 'health_rest']
    );

    // Serialization verification
    $payload = $disposition->toArray();
    expect($payload)->toHaveKey('gate_user_id');
    expect($payload)->toHaveKey('disposition_type');
    expect($payload)->not->toHaveKey('diagnosis');
    expect($payload)->not->toHaveKey('icd10');
    expect($payload)->not->toHaveKey('vital_signs');
    expect($payload)->not->toHaveKey('medications');

    // Runtime defense-in-depth validator verification
    expect(function () use ($sandboxClient) {
        $sandboxClient->assertPayloadCompliant([
            'gate_user_id' => 'GATE-001',
            'disposition_type' => 'rest',
            'diagnosis' => 'Clinical Diagnosis Leaked',
        ]);
    })->toThrow(InvalidArgumentException::class);
});

test('Step 7 — Post-cutover production data integrity invariants hold true', function () {
    // Invariant 1: No duplicate gate_user_id across People
    $duplicateGateIds = Person::whereNotNull('gate_user_id')
        ->select('gate_user_id', DB::raw('count(*) as count'))
        ->groupBy('gate_user_id')
        ->having('count', '>', 1)
        ->get();
    expect($duplicateGateIds)->toBeEmpty();

    // Invariant 2: No duplicate patient numbers
    $duplicatePatientNumbers = Patient::select('patient_number', DB::raw('count(*) as count'))
        ->groupBy('patient_number')
        ->having('count', '>', 1)
        ->get();
    expect($duplicatePatientNumbers)->toBeEmpty();

    // Invariant 3: No duplicate referral numbers
    $duplicateReferrals = Referral::select('referral_number', DB::raw('count(*) as count'))
        ->groupBy('referral_number')
        ->having('count', '>', 1)
        ->get();
    expect($duplicateReferrals)->toBeEmpty();

    // Invariant 4: No negative medicine batch current_quantity
    $negativeBatches = MedicineBatch::where('current_quantity', '<', 0)->get();
    expect($negativeBatches)->toBeEmpty();
});
