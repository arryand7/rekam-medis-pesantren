<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ObservationService;

test('authorized officer can start observation episode for assessment_completed visit', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam tinggi 38.8C dan badan lemas',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam sejak semalam',
        'examination_findings' => 'Suhu 38.8C',
        'assessment_summary' => 'Febris suspek ISPA',
        'disposition_recommendation' => 'observation_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    expect($visit->fresh()->status)->toBe('assessment_completed');

    $obsService = new ObservationService;
    $episode = $obsService->startEpisode($visit, [
        'reason' => 'Pemantauan suhu tubuh dan tirah baring Poskestren',
        'location_label' => 'Ruang Observasi Putra Bed 01',
    ], $officer);

    expect($episode->status)->toBe('active');
    expect($episode->responsible_officer_id)->toBe($officer->id);
    expect($visit->fresh()->status)->toBe('under_observation');
});

test('active observation guard prevents duplicate active episodes for same visit', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Pusing dan lemas',
    ], $officer);

    $obsService = new ObservationService;
    $obsService->startEpisode($visit, [
        'reason' => 'Observasi pertama',
    ], $officer);

    // Second active observation for same visit should throw Exception
    expect(fn () => $obsService->startEpisode($visit, [
        'reason' => 'Observasi kedua ganda',
    ], $officer))->toThrow(Exception::class);
});
