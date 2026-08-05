<?php

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientHealthProfile;
use App\Models\Person;

test('patient has structured health profile and active allergies', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id]);

    $profile = PatientHealthProfile::create([
        'patient_id' => $patient->id,
        'blood_type' => 'A+',
        'emergency_notes' => 'Alergi dingin',
    ]);

    $allergy = PatientAllergy::create([
        'patient_id' => $patient->id,
        'allergen' => 'Penicillin',
        'reaction' => 'Ruam merah',
        'severity' => 'severe',
        'status' => 'confirmed',
    ]);

    expect($patient->healthProfile->blood_type)->toBe('A+');
    expect($patient->activeAllergies->count())->toBe(1);
    expect($patient->activeAllergies->first()->allergen)->toBe('Penicillin');
});

test('allergy status entered-in-error does not hard delete record', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id]);

    $allergy = PatientAllergy::create([
        'patient_id' => $patient->id,
        'allergen' => 'Aspirin',
        'status' => 'confirmed',
    ]);

    // Update status to entered-in-error instead of deleting
    $allergy->update(['status' => 'entered-in-error']);

    expect($patient->allergies()->count())->toBe(1);
    expect($patient->activeAllergies()->count())->toBe(0);
    expect($patient->allergies->first()->status)->toBe('entered-in-error');
});
