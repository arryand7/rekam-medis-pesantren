<?php

use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\OperationalNotification;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\OperationalNotificationService;

beforeEach(function () {
    FakeAttendanceIntegration::reset();
});

test('operational notification can be prepared and acknowledged manually', function () {
    $service = new OperationalNotificationService;
    $person = Person::factory()->create();
    $officer = User::factory()->create();
    $dormSupervisor = User::factory()->create();

    $notif = $service->prepareNotification([
        'person_id' => $person->id,
        'notification_type' => 'rest_restriction',
        'recipient_type' => 'dorm_supervisor',
        'payload_snapshot' => [
            'rest_recommendation' => 'Istirahat di asrama',
            'practical_instructions' => 'Jangan piket berat',
        ],
    ], $officer);

    expect($notif->status)->toBe('prepared');
    expect($notif->prepared_by_id)->toBe($officer->id);

    $acknowledged = $service->acknowledgeNotification($notif, 'Santri telah berada di kamar asrama', $dormSupervisor);

    expect($acknowledged->status)->toBe('acknowledged');
    expect($acknowledged->acknowledged_by_id)->toBe($dormSupervisor->id);
    expect($acknowledged->acknowledgement_notes)->toBe('Santri telah berada di kamar asrama');
});

test('operational notification cancellation is recorded and audited', function () {
    $service = new OperationalNotificationService;
    $person = Person::factory()->create();
    $officer = User::factory()->create();

    $notif = $service->prepareNotification([
        'person_id' => $person->id,
        'notification_type' => 'limited_activity',
        'recipient_type' => 'homeroom_teacher',
        'payload_snapshot' => ['practical_instructions' => 'KBM duduk'],
    ], $officer);

    $cancelled = $service->cancelNotification($notif, 'Instruksi dibatalkan dokter penanggung jawab', $officer);

    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->cancellation_reason)->toBe('Instruksi dibatalkan dokter penanggung jawab');

    // Cannot acknowledge a cancelled notification
    expect(fn () => $service->acknowledgeNotification($cancelled, 'Notes', $officer))
        ->toThrow(Exception::class, 'Tidak dapat mengonfirmasi notifikasi yang telah dibatalkan.');
});

test('discharge auto-dispatches dorm and homeroom operational notifications', function () {
    $person = Person::factory()->create(['gate_user_id' => 'GATE-DISCHARGE-NOTIF']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $officer = User::factory()->create();

    $visit = MedicalVisit::create([
        'visit_number' => 'VIS-AUTO-NOTIF',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Demam',
        'created_by_id' => $officer->id,
    ]);

    $discharge = VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama',
        'clinical_summary' => 'Demam mereda',
        'final_condition' => 'Membaik',
        'activity_recommendation' => 'light_activity',
        'rest_recommendation' => 'Istirahat malam lebih awal',
        'restriction_notes' => 'Hindari piket malam',
        'status' => 'finalized',
        'prepared_by_id' => $officer->id,
    ]);

    $restriction = ActivityRestriction::create([
        'visit_discharge_id' => $discharge->id,
        'activity_status' => 'light_activity',
        'effective_start' => now(),
        'effective_until' => now()->addDay(),
        'restriction_type' => 'dormitory_chores',
        'restriction_notes' => 'Bebas piket',
        'status' => 'active',
        'issued_by_id' => $officer->id,
        'issued_at' => now(),
    ]);

    $service = new OperationalNotificationService;
    $result = $service->dispatchDischargeNotifications($discharge, $officer);

    expect($result['notifications'])->toHaveCount(2); // 1 dorm, 1 homeroom
    expect($result['outbox_event'])->not->toBeNull(); // 1 attendance outbox event

    $dormNotif = OperationalNotification::where('person_id', $person->id)->where('recipient_type', 'dorm_supervisor')->first();
    expect($dormNotif)->not->toBeNull();
    expect($dormNotif->payload_snapshot['rest_recommendation'])->toBe('Istirahat malam lebih awal');

    $teacherNotif = OperationalNotification::where('person_id', $person->id)->where('recipient_type', 'homeroom_teacher')->first();
    expect($teacherNotif)->not->toBeNull();
});
