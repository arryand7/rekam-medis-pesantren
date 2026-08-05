<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\ClinicalConsultationService;
use App\Services\MedicalVisitService;

test('recording external advice and formulating local clinical decision completes consultation', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Pusing dan lemas',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Lemas sejak tadi pagi',
        'examination_findings' => 'TD 100/60 mmHg',
        'assessment_summary' => 'Hipotensi ringan',
        'disposition_recommendation' => 'rest_at_poskestren',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $consultationService = new ClinicalConsultationService;
    $partner = $consultationService->createPartner([
        'code' => 'PKM-DEKOS',
        'name' => 'Puskesmas Dekat Ponpes',
        'partner_type' => 'puskesmas',
    ], $officer);

    $consultation = $consultationService->createConsultation($visit, [
        'healthcare_partner_id' => $partner->id,
        'purpose' => 'Konsultasi hipotensi santri',
        'clinical_question' => 'Advis cairan dan penanganan istirahat',
        'urgency' => 'routine',
    ], $officer);

    $consultationService->transmitConsultation($consultation, $officer);

    // Record External Advice
    $advice = $consultationService->recordExternalAdvice($consultation, [
        'clinician_name' => 'dr. Anton',
        'clinician_profession' => 'Dokter Umum Puskesmas',
        'advice_text' => 'Berikan hidrasi oral cukup dan istirahat 12 jam. Bila TD < 90/60 rujuk.',
    ], $officer);

    expect($advice->clinician_name)->toBe('dr. Anton');
    expect($consultation->fresh()->status)->toBe('responded');

    // Record Local Clinical Decision
    $decision = $consultationService->recordLocalDecision($consultation, [
        'decision_type' => 'continue_observation',
        'rationale' => 'Menyetujui advis dokter puskesmas untuk observasi & rehidrasi di Poskestren.',
    ], $officer);

    expect($decision->decision_type)->toBe('continue_observation');
    expect($consultation->fresh()->status)->toBe('completed');
    expect($visit->fresh()->status)->toBe('external_consultation_completed');
});
