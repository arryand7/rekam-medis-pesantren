<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;

test('person can have user and patient relationship', function () {
    $person = Person::factory()->create(['name' => 'Ahmad Santri']);
    $user = User::factory()->create(['person_id' => $person->id]);
    $patient = Patient::factory()->create(['person_id' => $person->id]);

    expect($person->user->id)->toBe($user->id);
    expect($person->patient->id)->toBe($patient->id);
    expect($user->person->id)->toBe($person->id);
    expect($patient->person->id)->toBe($person->id);
});

test('all human persons including admin are eligible as patient', function () {
    $adminPerson = Person::factory()->create(['user_type' => 'admin']);
    $santriPerson = Person::factory()->create(['user_type' => 'santri']);
    $botPerson = Person::factory()->create(['user_type' => 'service_account']);

    expect($adminPerson->isHumanPatientEligible())->toBeTrue();
    expect($santriPerson->isHumanPatientEligible())->toBeTrue();
    expect($botPerson->isHumanPatientEligible())->toBeFalse();
});

test('user deactivation does not delete person or patient record', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $user = User::factory()->create(['person_id' => $person->id, 'is_active' => true]);

    // Deactivate user
    $user->update(['is_active' => false]);

    expect($user->fresh()->is_active)->toBeFalse();
    expect(Person::find($person->id))->not->toBeNull();
    expect(Patient::find($patient->id))->not->toBeNull();
});
