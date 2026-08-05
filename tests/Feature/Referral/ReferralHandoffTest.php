<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;

function createFinalizedReferralForHandoffTest(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Trauma kepala',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Jatuh dari tangga, kepala terbentur',
        'examination_findings' => 'Luka robek, GCS 14',
        'assessment_summary' => 'Trauma kepala ringan-sedang',
        'working_diagnosis' => 'Head trauma',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-HANDOFF-TEST',
        'name' => 'RS Handoff Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Trauma kepala, perlu CT Scan dan observasi',
        'clinical_summary' => 'Trauma kepala, GCS 14, perlu evaluasi neurologi',
    ], $officer);

    $referralService->recordDeparture($referral, [], $officer);

    return [$referralService, $referral->fresh(), $officer];
}

test('handover is idempotent — same idempotency key returns existing record', function () {
    [$referralService, $referral, $officer] = createFinalizedReferralForHandoffTest();

    $handover1 = $referralService->recordHandover($referral, [
        'idempotency_key' => 'handoff-unique-001',
        'notes' => 'Diserahterimakan ke perawat jaga',
    ], $officer);

    $handover2 = $referralService->recordHandover($referral, [
        'idempotency_key' => 'handoff-unique-001',
        'notes' => 'Diserahterimakan ke perawat jaga (retry)',
    ], $officer);

    expect($handover1->id)->toBe($handover2->id);
});

test('destination status event arrived updates referral arrived_at_destination', function () {
    [$referralService, $referral, $officer] = createFinalizedReferralForHandoffTest();

    $event = $referralService->recordStatusEvent($referral, [
        'event_type' => 'arrived',
        'contact_attribution' => 'Petugas IGD RS',
        'notes' => 'Pasien tiba di IGD',
    ], $officer);

    expect($event->event_type)->toBe('arrived');
    expect($referral->fresh()->status)->toBe('arrived');
    expect($referral->fresh()->arrived_at_destination)->not->toBeNull();
});

test('handoff does not equal acceptance — accepted status requires separate status event', function () {
    [$referralService, $referral, $officer] = createFinalizedReferralForHandoffTest();

    $handover = $referralService->recordHandover($referral, [
        'notes' => 'Dokumen dikirimkan via petugas',
    ], $officer);

    // After handover, status is still 'departed' — NOT 'accepted'
    expect($referral->fresh()->status)->toBe('departed');

    // Acceptance requires explicit status event
    $referralService->recordStatusEvent($referral, [
        'event_type' => 'accepted',
        'contact_attribution' => 'dr. Sari, SpPD',
    ], $officer);

    expect($referral->fresh()->status)->toBe('accepted');
});
