<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\ClinicalConsultationService;
use App\Services\MedicalVisitService;

test('creating clinical consultation builds versioned summary snapshot with checksum', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam tinggi 3 hari',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam sejak 3 hari yang lalu',
        'examination_findings' => 'Suhu 38.8 C',
        'assessment_summary' => 'Demam typhoid suspected',
        'working_diagnosis' => 'Suspected Typhoid Fever',
        'disposition_recommendation' => 'observation_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $consultationService = new ClinicalConsultationService;
    $partner = $consultationService->createPartner([
        'code' => 'RSUD-SURABAYA',
        'name' => 'RSUD Kota Surabaya',
        'partner_type' => 'hospital',
    ], $officer);

    $consultation = $consultationService->createConsultation($visit, [
        'healthcare_partner_id' => $partner->id,
        'purpose' => 'Permohonan advis penanganan demam typhoid',
        'clinical_question' => 'Mohon advis pemberian antibiotik dan kebutuhan rujukan',
        'urgency' => 'routine',
    ], $officer);

    expect($consultation->status)->toBe('ready');
    expect($consultation->latestVersion->version_number)->toBe(1);
    expect($consultation->latestVersion->checksum)->not->toBeEmpty();
    expect($consultation->latestVersion->summary_payload['assessment_summary'])->toBe('Demam typhoid suspected');
});
