<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;

function createDepartedReferralForReturnTest(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Appendisitis',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri perut kanan bawah akut',
        'examination_findings' => 'McBurney (+), rebound tenderness (+)',
        'assessment_summary' => 'Appendisitis akut',
        'working_diagnosis' => 'Acute appendicitis',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-SURGERY',
        'name' => 'RS Bedah Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Appendisitis akut, perlu tindakan operatif',
        'clinical_summary' => 'Nyeri perut kanan bawah, tanda peritonismus, perlu appendektomi',
    ], $officer);

    $referralService->recordDeparture($referral, [], $officer);

    return [$referralService, $referral->fresh(), $officer];
}

test('return from referral records external outcome without auto-mutating local diagnosis', function () {
    [$referralService, $referral, $officer] = createDepartedReferralForReturnTest();

    $referralReturn = $referralService->recordReturn($referral, [
        'external_outcome_summary' => 'Pasien menjalani appendektomi laparoskopi, operasi berjalan lancar tanpa komplikasi',
        'external_diagnosis_text' => 'Appendicitis acuta perforasi',
        'external_procedures_text' => 'Laparoskopi appendektomi',
        'external_medication_instructions' => 'Amoxicillin 500mg 3x1 selama 7 hari, Paracetamol bila nyeri',
        'restrictions_text' => 'Istirahat 7 hari, tidak boleh olahraga berat 4 minggu',
        'follow_up_date' => now()->addDays(7)->toDateString(),
        'follow_up_facility' => 'Poli Bedah RS Bedah Test',
    ], $officer);

    expect($referralReturn->status)->toBe('returned');
    expect($referral->fresh()->status)->toBe('returned');

    // External diagnosis MUST NOT auto-update local assessment
    $localAssessment = $referral->clinicalAssessment;
    expect($localAssessment->fresh()->working_diagnosis)->toBe('Acute appendicitis'); // unchanged
});

test('only one return per referral is allowed', function () {
    [$referralService, $referral, $officer] = createDepartedReferralForReturnTest();

    $referralService->recordReturn($referral, [
        'external_outcome_summary' => 'Pasien pulang sehat',
    ], $officer);

    expect(fn () => $referralService->recordReturn($referral->fresh(), [
        'external_outcome_summary' => 'Kepulangan duplikat',
    ], $officer))->toThrow(Exception::class);
});

test('return only allowed from valid active referral status not from cancelled', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Flu biasa',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Flu ringan',
        'examination_findings' => 'Rinitis ringan',
        'assessment_summary' => 'ISPA ringan',
        'working_diagnosis' => 'URTI',
        'disposition_recommendation' => 'rest_at_poskestren',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-CANCELLED-TEST',
        'name' => 'RS Cancelled Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Rujukan yang akan dibatalkan',
        'clinical_summary' => 'Flu ringan perlu evaluasi',
    ], $officer);

    // Cancel the referral
    $referral->update(['status' => 'cancelled', 'cancellation_reason' => 'Pasien menolak rujukan']);

    // Cannot record return from a cancelled referral
    expect(fn () => $referralService->recordReturn($referral->fresh(), [
        'external_outcome_summary' => 'Ini tidak boleh berhasil',
    ], $officer))->toThrow(Exception::class, 'aktif');
});

test('local return review does not create discharge and external diagnosis does not auto-update local records', function () {
    [$referralService, $referral, $officer] = createDepartedReferralForReturnTest();

    $referralReturn = $referralService->recordReturn($referral, [
        'external_outcome_summary' => 'Appendektomi berhasil, pasien dalam kondisi baik',
        'external_medication_instructions' => 'Antibiotik 5 hari',
    ], $officer);

    $review = $referralService->recordReturnReview($referralReturn, [
        'review_summary' => 'Pasien kembali dalam kondisi stabil, luka operasi dalam kondisi baik, tidak ada tanda infeksi',
        'decision_type' => 'rest_recommended',
        'medication_reconciliation_note' => 'Antibiotik dari RS dilanjutkan, perlu verifikasi jenis dan dosis dengan staf medis Poskestren',
    ], $officer);

    expect($review->decision_type)->toBe('rest_recommended');
    expect($review->status)->toBe('finalized');
    expect($referral->fresh()->status)->toBe('completed');
    expect($referral->fresh()->medicalVisit->status)->toBe('referral_review_completed');

    // Verify visit is NOT closed/discharged — no discharge record should be auto-created
    // The visit status is 'referral_review_completed' NOT 'discharged'
    expect($referral->fresh()->medicalVisit->status)->not->toBe('discharged');
    expect($referral->fresh()->medicalVisit->status)->not->toBe('completed');
});
