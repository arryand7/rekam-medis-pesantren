<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;

test('transport can be arranged for a prepared referral', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Batuk darah',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Batuk disertai darah',
        'examination_findings' => 'Ronkhi kasar, dahak merah',
        'assessment_summary' => 'Hemoptisis suspect TB paru',
        'working_diagnosis' => 'Hemoptysis suspect PTB',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-TB-01',
        'name' => 'RS Paru',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Hemoptisis, perlu evaluasi dokter spesialis paru',
        'clinical_summary' => 'Batuk darah, ronkhi kasar, riwayat kontak TB',
    ], $officer);

    $transport = $referralService->arrangeTransport($referral, [
        'transport_type' => 'school_vehicle',
        'vehicle_identifier' => 'B 1234 ZZ',
        'driver_name' => 'Pak Soleh',
        'driver_contact' => '08111222333',
    ], $officer);

    expect($transport->status)->toBe('planned');
    expect($transport->transport_type)->toBe('school_vehicle');
    expect($transport->driver_name)->toBe('Pak Soleh');
});

test('primary companion uniqueness is enforced per active referral', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Vertigo berat',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Vertigo berputar tiba-tiba',
        'examination_findings' => 'Nistagmus (+)',
        'assessment_summary' => 'Vertigo suspect BPPV',
        'working_diagnosis' => 'BPPV',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-NEURO',
        'name' => 'RS Neurologi',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Vertigo untuk evaluasi neurologi',
        'clinical_summary' => 'Vertigo berputar, nistagmus (+)',
    ], $officer);

    // First primary companion — OK
    $referralService->assignCompanion($referral, [
        'name_snapshot' => 'Ustadz Ahmad',
        'role_relationship' => 'Pengasuh Asrama',
        'phone' => '08100000001',
        'is_primary' => true,
    ], $officer);

    // Second primary companion on same referral — must fail
    expect(fn () => $referralService->assignCompanion($referral, [
        'name_snapshot' => 'Ustad Budi',
        'role_relationship' => 'Wali Santri',
        'is_primary' => true,
    ], $officer))->toThrow(Exception::class, 'pendamping utama');
});

test('departure sets server-authoritative timestamp and updates visit status', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Luka bakar',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Luka bakar air panas',
        'examination_findings' => 'Luka grade II, 15% TBSA',
        'assessment_summary' => 'Luka bakar derajat 2 luas',
        'working_diagnosis' => 'Burn injury grade 2',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-BURN',
        'name' => 'RS Luka Bakar',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'emergency',
        'reason' => 'Luka bakar luas memerlukan perawatan khusus',
        'clinical_summary' => 'Luka bakar 15% TBSA grade 2 perlu wound care dan cairan intravena',
    ], $officer);

    $departed = $referralService->recordDeparture($referral, [], $officer);

    expect($departed->status)->toBe('departed');
    expect($departed->departed_at)->not->toBeNull();
    expect($visit->fresh()->status)->toBe('referred_external');
});
