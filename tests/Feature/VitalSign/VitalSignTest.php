<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\VitalSignService;

test('vital signs service records physical measurements with validation', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan pusing',
    ], $officer);

    $vitalService = new VitalSignService;

    $vital = $vitalService->record($visit, [
        'temperature_c' => 37.8,
        'systolic_bp' => 120,
        'diastolic_bp' => 80,
        'pulse_bpm' => 84,
        'spo2_percent' => 98,
        'finalize' => true,
    ], $officer);

    expect($vital->status)->toBe('finalized');
    expect($vital->temperature_c)->toBe(37.8);
    expect($vital->isFinalized())->toBeTrue();
});

test('vital signs rejects physical bounds outside normal physiological limits', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Tes suhu ekstrem',
    ], $officer);

    $vitalService = new VitalSignService;

    // Invalid temperature 55°C
    expect(fn () => $vitalService->record($visit, [
        'temperature_c' => 55.0,
    ], $officer))->toThrow(Exception::class);
});
