<?php

use App\Models\HealthcarePartner;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;

test('referral can be created with finalized assessment, generates unique referral number', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan nyeri kepala',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam 3 hari berturut-turut',
        'examination_findings' => 'Suhu 39.2 C, HR 100/mnt',
        'assessment_summary' => 'Demam tinggi memerlukan evaluasi lanjutan',
        'working_diagnosis' => 'Fever, unknown origin',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RSUD-TEST',
        'name' => 'RSUD Kota Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Demam tinggi tidak turun 3 hari, perlu evaluasi laboratorium lanjutan',
        'clinical_summary' => 'Pasien santri demam 39 derajat, tidak turun dengan parasetamol, perlu cek darah lengkap dan kultur',
    ], $officer);

    expect($referral->referral_number)->toStartWith('REF-');
    expect($referral->status)->toBe('prepared');
    expect($referral->latestVersion)->not->toBeNull();
    expect($referral->latestVersion->version_number)->toBe(1);
    expect($referral->latestVersion->checksum)->not->toBeEmpty();
    expect($visit->fresh()->status)->toBe('referral_prepared');
});

test('referral requires finalized assessment', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Nyeri perut',
    ], $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RSUD-TEST2',
        'name' => 'RSUD Test 2',
        'partner_type' => 'hospital',
    ], $officer);

    // No finalized assessment — should fail
    expect(fn () => $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Test alasan',
        'clinical_summary' => 'Test ringkasan klinis minimal',
    ], $officer))->toThrow(Exception::class, 'finalisasi');
});

test('one active referral guard blocks duplicate active referral for same visit', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Sesak nafas',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Sesak napas progresif',
        'examination_findings' => 'Ronkhi (+)',
        'assessment_summary' => 'Pneumonia suspect',
        'working_diagnosis' => 'Pneumonia',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-GUARD-TEST',
        'name' => 'RS Guard Test',
        'partner_type' => 'hospital',
    ], $officer);

    // First referral — OK
    $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Sesak napas memerlukan rawat inap',
        'clinical_summary' => 'Pasien sesak napas, ronkhi (+), kemungkinan pneumonia',
    ], $officer);

    // Second referral — must be blocked
    expect(fn () => $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Duplikat rujukan',
        'clinical_summary' => 'Ini tidak boleh berhasil dibuat',
    ], $officer))->toThrow(Exception::class, 'aktif');
});

test('emergency referral does not require existing consultation', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Tidak sadar mendadak',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Tiba-tiba tidak sadar',
        'examination_findings' => 'GCS 8',
        'assessment_summary' => 'Penurunan kesadaran',
        'working_diagnosis' => 'Decreased consciousness, unknown etiology',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-EMERGENCY',
        'name' => 'RS Emergency Test',
        'partner_type' => 'hospital',
    ], $officer);

    // Emergency — no consultation_id, should succeed immediately
    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'emergency',
        'reason' => 'Penurunan kesadaran mendadak, perlu penanganan segera di IGD RS',
        'clinical_summary' => 'Pasien tidak sadar, GCS 8, perlu evaluasi dan stabilisasi segera',
        // No clinical_consultation_id — emergency does NOT require consultation
    ], $officer);

    expect($referral->urgency)->toBe('emergency');
    expect($referral->clinical_consultation_id)->toBeNull();
    expect($referral->status)->toBe('prepared');
});

test('referral number is unique and uses ULID-based opaque suffix', function () {
    $numbers = array_map(fn () => Referral::generateReferralNumber(), range(1, 20));
    expect(count(array_unique($numbers)))->toBe(20);
    foreach ($numbers as $num) {
        expect($num)->toMatch('/^REF-\d{8}-[A-Z0-9]{8}$/');
    }
});

test('referral create page renders successfully with active healthcare partners passed to view', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);

    $permission = Permission::firstOrCreate(['name' => 'create-referrals'], ['display_name' => 'Buat Rujukan']);
    $role = Role::firstOrCreate(['name' => 'petugas_medis_test'], ['display_name' => 'Petugas Medis Test']);
    $role->permissions()->syncWithoutDetaching([$permission->id]);

    $officer = User::factory()->create();
    $officer->roles()->syncWithoutDetaching([$role->id]);

    $partner = HealthcarePartner::create([
        'code' => 'RSUD-KOTA-TEST',
        'name' => 'RSUD Kota Mitra Test',
        'partner_type' => 'hospital',
        'is_active' => true,
    ]);

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Evaluasi rujukan',
    ], $officer);

    $response = $this->actingAs($officer)->get(route('visits.referrals.create', $visit->id));

    $response->assertOk();
    $response->assertViewHas('partners');
    $response->assertSee('RSUD Kota Mitra Test');
});
