<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;

test('initial actions records non-medication interventions', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Luka lecet di lutut akibat jatuh',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $action = $assessmentService->recordAction($visit, [
        'action_type' => 'wound_care',
        'description' => 'Pembersihan luka dengan antiseptik dan pembebatan steril',
    ], $officer);

    expect($action->action_type)->toBe('wound_care');
    expect($action->description)->toBe('Pembersihan luka dengan antiseptik dan pembebatan steril');
    expect($action->status)->toBe('performed');
    expect($visit->actions->count())->toBe(1);
});
