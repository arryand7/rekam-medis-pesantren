<?php

use App\Actions\Discharge\EvaluateVisitDischargeReadinessAction;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;

function createDischargeTestVisit(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan pusing 2 hari',
    ], $officer);

    return [$visit, $officer];
}

test('visit without assessment is blocked from discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Pengkajian klinis (clinical assessment) belum dibuat untuk kunjungan ini.');
});

test('visit with draft assessment is blocked from discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $assessmentService = new ClinicalAssessmentService;
    $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam akut',
        'examination_findings' => 'Suhu 38.5C',
        'assessment_summary' => 'Febris susp viral',
        'working_diagnosis' => 'Febris',
        'disposition_recommendation' => 'home_care',
    ], $officer);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Pengkajian klinis masih berstatus draf. Finalisasi pengkajian klinis terlebih dahulu.');
});

test('cancelled visit is blocked from discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();
    $visit->update(['status' => 'cancelled']);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Kunjungan telah dibatalkan dan tidak dapat dilakukan proses kepulangan (discharge).');
});

test('active observation episode blocks discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam akut',
        'examination_findings' => 'Suhu 38.5C',
        'assessment_summary' => 'Febris susp viral',
        'working_diagnosis' => 'Febris',
        'disposition_recommendation' => 'observation_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    ObservationEpisode::create([
        'medical_visit_id' => $visit->id,
        'location_label' => 'Ruang Observasi',
        'reason' => 'Monitoring demam',
        'status' => 'active',
        'started_at' => now(),
        'responsible_officer_id' => $officer->id,
    ]);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Masih terdapat episode observasi yang aktif. Selesaikan atau batalkan episode observasi terlebih dahulu.');
});

test('in-flight referral blocks discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri perut akut',
        'examination_findings' => 'Defans muskular',
        'assessment_summary' => 'Akut abdomen',
        'working_diagnosis' => 'Appendisitis akut',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-APP-'.uniqid(),
        'name' => 'RS Bedah Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Evaluasi bedah digestif',
        'clinical_summary' => 'Nyeri perut kuadran kanan bawah',
    ], $officer);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Terdapat rujukan eksternal yang masih aktif atau dalam proses. Selesaikan alur rujukan sebelum menutup kunjungan.');
});

test('returned referral without return review blocks discharge', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri perut',
        'examination_findings' => 'Defans muskular',
        'assessment_summary' => 'Akut abdomen',
        'working_diagnosis' => 'Appendisitis',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-APP2-'.uniqid(),
        'name' => 'RS Bedah 2 Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Evaluasi bedah',
        'clinical_summary' => 'Nyeri perut',
    ], $officer);

    // Transition referral through departure to return
    $referralService->recordDeparture($referral, ['transport_type' => 'ambulance'], $officer);
    $referralService->recordHandover($referral, ['idempotency_key' => 'HANDOVER-TEST-'.uniqid(), 'receiving_personnel_name' => 'Dr. Bedah'], $officer);
    $referralService->recordReturn($referral, [
        'return_condition' => 'recovered',
        'external_outcome_summary' => 'Pasien telah diobservasi dan diperbolehkan pulang',
    ], $officer);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeFalse();
    expect($result['technical_blockers'])->toContain('Terdapat rujukan yang telah kembali dari faskes tujuan namun belum dilakukan tinjauan klinis lokal (return review).');
});

test('visit is ready when finalized assessment exists and all sub-workflows completed', function () {
    [$visit, $officer] = createDischargeTestVisit();

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Batuk pilek ringan',
        'examination_findings' => 'Faring hiperemis ringan',
        'assessment_summary' => 'ISPA ringan',
        'working_diagnosis' => 'Common Cold',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $evaluator = new EvaluateVisitDischargeReadinessAction;
    $result = $evaluator->execute($visit);

    expect($result['is_ready'])->toBeTrue();
    expect($result['technical_blockers'])->toBeEmpty();
});
