<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\ObservationService;

test('completing observation episode records outcome and transitions visit status to observation_completed', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan lemas',
    ], $officer);

    $obsService = new ObservationService;
    $episode = $obsService->startEpisode($visit, [
        'reason' => 'Observasi pemulihan',
    ], $officer);

    $completedEpisode = $obsService->completeEpisode(
        $episode,
        'return_to_activity_recommended',
        'Suhu tubuh stabil 36.6C, santri sudah dapat beraktivitas kembali di kelas',
        $officer
    );

    expect($completedEpisode->status)->toBe('completed');
    expect($completedEpisode->outcome)->toBe('return_to_activity_recommended');
    expect($visit->fresh()->status)->toBe('observation_completed');
});
