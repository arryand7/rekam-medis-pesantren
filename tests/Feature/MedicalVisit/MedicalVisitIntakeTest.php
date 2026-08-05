<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\MedicalVisitService;

test('authorized user can register medical visit with server timestamp and unique visit number', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $service = new MedicalVisitService;
    $visit = $service->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam tinggi dan sakit kepala sejak tadi pagi',
        'reporting_type' => 'dormitory_guardian',
        'origin_location' => 'Asrama Al-Ghazali 201',
    ], $officer);

    expect($visit->visit_number)->toStartWith('VIS-');
    expect($visit->status)->toBe('waiting_assessment');
    expect($visit->patient_id)->toBe($patient->id);
    expect($visit->arrived_at)->not->toBeNull();
});

test('active visit guard prevents duplicate active visits for same patient unless overridden', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $service = new MedicalVisitService;

    // First visit
    $service->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Sakit perut',
    ], $officer);

    // Attempting second active visit without override should throw Exception
    expect(fn () => $service->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Batuk pilek',
    ], $officer))->toThrow(Exception::class);

    // Registering with override and reason should succeed
    $overrideVisit = $service->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Sesak napas darurat',
        'override_active' => true,
        'override_reason' => 'Kondisi darurat baru saat menunggu assessment lama',
    ], $officer);

    expect($overrideVisit)->not->toBeNull();
});

test('visit cancellation updates status non-destructively with reason', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $service = new MedicalVisitService;
    $visit = $service->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Salah panggil nama santri',
    ], $officer);

    $cancelledVisit = $service->cancelVisit($visit, 'Kesalahan registrasi identitas pasien', $officer);

    expect($cancelledVisit->status)->toBe('cancelled');
    expect($cancelledVisit->cancellation_reason)->toBe('Kesalahan registrasi identitas pasien');
    expect($cancelledVisit->isActive())->toBeFalse();
});
