<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\ObservationService;

test('officer can record periodic monitoring entry for active observation', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Observasi demam',
    ], $officer);

    $obsService = new ObservationService;
    $episode = $obsService->startEpisode($visit, [
        'reason' => 'Pemantauan suhu berkala',
    ], $officer);

    $record = $obsService->recordMonitoring($episode, [
        'condition_summary' => 'Santri sudah minum air hangat, suhu tubuh tampak menurun',
        'general_condition' => 'good',
    ], $officer);

    expect($record->condition_summary)->toContain('suhu tubuh tampak menurun');
    expect($record->status)->toBe('finalized');
    expect($episode->records->count())->toBe(1);
});
