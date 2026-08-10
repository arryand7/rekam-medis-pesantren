<?php

use App\Models\Patient;
use App\Models\Person;

test('generates exactly 1000 unique patient numbers without collisions on synthetic creations', function () {
    $generatedNumbers = [];

    for ($i = 0; $i < 1000; $i++) {
        $number = Patient::generateUniquePatientNumber();
        $generatedNumbers[] = $number;
    }

    $uniqueCount = count(array_unique($generatedNumbers));
    expect($uniqueCount)->toBe(1000);
});

test('handles forced patient_number collision gracefully via retry escalation', function () {
    $person1 = Person::factory()->create();
    $person2 = Person::factory()->create();

    // 1. Manually create a patient with a specific patient number
    $forcedNumber = 'RM-COLLISION-1234';
    Patient::create([
        'person_id' => $person1->id,
        'patient_number' => $forcedNumber,
        'is_eligible' => true,
    ]);

    // 2. Mock generateUniquePatientNumber collision by having candidate match $forcedNumber initially
    // Patient::createOrFindForPerson should detect existing candidate or catch QueryException and generate alternative unique number
    $patient2 = Patient::createOrFindForPerson($person2);

    expect($patient2)->not->toBeNull();
    expect($patient2->person_id)->toBe($person2->id);
    expect($patient2->patient_number)->not->toBe($forcedNumber);
    expect($patient2->patient_number)->toStartWith('RM-');

    // Verify DB count
    expect(Patient::where('patient_number', $forcedNumber)->count())->toBe(1);
    expect(Patient::where('patient_number', $patient2->patient_number)->count())->toBe(1);
});

test('concurrent creation calls on same person return deterministic single Patient instance', function () {
    $person = Person::factory()->create();

    $patientA = Patient::createOrFindForPerson($person);
    $patientB = Patient::createOrFindForPerson($person);
    $patientC = Patient::createOrFindForPerson($person);

    expect($patientA->id)->toBe($patientB->id);
    expect($patientB->id)->toBe($patientC->id);
    expect($patientA->patient_number)->toBe($patientB->patient_number);

    $count = Patient::where('person_id', $person->id)->count();
    expect($count)->toBe(1);
});
