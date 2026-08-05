<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;

test('clinical assessment draft saving updates visit status to under_assessment', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Nyeri perut sebelah kanan',
    ], $officer);

    expect($visit->status)->toBe('waiting_assessment');

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri perut dirasakan sejak 2 jam lalu setelah makan pedas',
        'examination_findings' => 'Nyeri tekan epigastrium (+), bising usus normal',
        'assessment_summary' => 'Suspek Gastritis Akut',
        'disposition_recommendation' => 'rest_at_poskestren',
    ], $officer);

    expect($assessment->status)->toBe('draft');
    expect($visit->fresh()->status)->toBe('under_assessment');
});

test('finalizing clinical assessment locks record and updates visit status to assessment_completed', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Batuk pilek dan radang tenggorokan',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Batuk berdahak 3 hari',
        'examination_findings' => 'Faring hiperemis (+)',
        'assessment_summary' => 'ISPA ringan',
        'disposition_recommendation' => 'return_to_activity',
    ], $officer);

    $finalizedAssessment = $assessmentService->finalizeAssessment($assessment, $officer);

    expect($finalizedAssessment->status)->toBe('finalized');
    expect($finalizedAssessment->finalized_at)->not->toBeNull();
    expect($visit->fresh()->status)->toBe('assessment_completed');
});
