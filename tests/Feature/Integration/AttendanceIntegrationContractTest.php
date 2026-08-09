<?php

use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\IntegrationIdentityConflict;
use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\OperationalNotificationService;

beforeEach(function () {
    FakeAttendanceIntegration::reset();
});

test('fake attendance adapter publishes disposition and returns external reference', function () {
    $adapter = new FakeAttendanceIntegration;

    $dto = new AttendanceHealthDispositionDTO(
        eventId: 'EVT-TEST-PUB-1',
        eventVersion: 1,
        gateUserId: 'GATE-USER-999',
        dispositionType: 'rest',
        effectiveFrom: now(),
        effectiveUntil: now()->addDays(2),
        activityScope: 'all_activities',
        sourceVisitReference: 'VISIT-TEST-100',
        issuedAt: now(),
    );

    $response = $adapter->publishDisposition($dto);

    expect($response['success'])->toBeTrue();
    expect($response['status_code'])->toBe(200);
    expect($response['external_reference'])->toStartWith('ABS-');

    $dispositions = FakeAttendanceIntegration::getPublishedDispositions();
    expect($dispositions)->toHaveKey('EVT-TEST-PUB-1');
});

test('supersede disposition links original event ID properly in fake adapter', function () {
    $adapter = new FakeAttendanceIntegration;

    $originalEventId = 'EVT-ORIGINAL-001';

    $newDto = new AttendanceHealthDispositionDTO(
        eventId: 'EVT-SUPERSEDED-002',
        eventVersion: 2,
        gateUserId: 'GATE-USER-999',
        dispositionType: 'limited_activity',
        effectiveFrom: now(),
        effectiveUntil: now()->addDay(),
        activityScope: 'sports_only',
        sourceVisitReference: 'VISIT-TEST-100',
        issuedAt: now(),
        supersedesEventId: $originalEventId
    );

    $response = $adapter->supersedeDisposition($originalEventId, $newDto);

    expect($response['success'])->toBeTrue();
    expect($response['external_reference'])->toStartWith('ABS-SUP-');
});

test('missing gate_user_id creates integration identity conflict instead of crashing or guessing', function () {
    $personWithoutGate = Person::factory()->create(['name' => 'Santri Tanpa Gate ID', 'gate_user_id' => null]);
    $patient = Patient::factory()->create(['person_id' => $personWithoutGate->id]);
    $officer = User::factory()->create();

    $visit = MedicalVisit::create([
        'visit_number' => 'VISIT-NO-GATE-01',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Evaluasi',
        'created_by_id' => $officer->id,
    ]);

    $discharge = VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama',
        'clinical_summary' => 'Kondisi baik',
        'final_condition' => 'Sembuh',
        'activity_recommendation' => 'full_activity',
        'status' => 'finalized',
        'prepared_by_id' => $officer->id,
    ]);

    $service = new OperationalNotificationService;
    $result = $service->dispatchDischargeNotifications($discharge, $officer);

    // Outbox event must be null because Gate ID was missing
    expect($result['outbox_event'])->toBeNull();

    // Identity conflict must be recorded
    $conflict = IntegrationIdentityConflict::where('person_id', $personWithoutGate->id)->first();
    expect($conflict)->not->toBeNull();
    expect($conflict->conflict_type)->toBe('missing_gate_user_id');
    expect($conflict->status)->toBe('open');
});
