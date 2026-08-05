<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\ClinicalConsultationService;
use App\Services\MedicalVisitService;

test('transmitting consultation request creates transmission record and updates status to sent', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Nyeri perut kanan bawah',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri perut mendadak',
        'examination_findings' => 'Nyeri tekan rebound (+)',
        'assessment_summary' => 'Suspek appendicitis akut',
        'working_diagnosis' => 'Appendicitis',
        'disposition_recommendation' => 'observation_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $consultationService = new ClinicalConsultationService;
    $partner = $consultationService->createPartner([
        'code' => 'PKM-RUMAH-SAKIT',
        'name' => 'RS Mitra Husada',
        'partner_type' => 'hospital',
    ], $officer);

    $consultation = $consultationService->createConsultation($visit, [
        'healthcare_partner_id' => $partner->id,
        'purpose' => 'Konsultasi rujukan cito',
        'clinical_question' => 'Apakah diperlukan persiapan operasi segera?',
        'urgency' => 'urgent',
    ], $officer);

    $transmission = $consultationService->transmitConsultation($consultation, $officer);

    expect($transmission->status)->toBe('sent');
    expect($consultation->fresh()->status)->toBe('sent');
    expect($transmission->idempotency_key)->not->toBeEmpty();
});
