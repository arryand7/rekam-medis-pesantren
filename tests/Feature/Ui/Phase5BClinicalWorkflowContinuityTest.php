<?php

use App\Models\ClinicalAssessment;
use App\Models\ClinicalConsultation;
use App\Models\ExternalClinicalAdvice;
use App\Models\HealthcarePartner;
use App\Models\MedicalVisit;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\ObservationRecord;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Referral;
use App\Models\ReferralTransport;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;
use App\Models\VitalSign;

function makePhase5BUser(array $permNames, string $name = 'Clinical Staff'): User
{
    $role = Role::create([
        'name' => 'phase5b_role_'.uniqid(),
        'display_name' => 'Phase 5B Role '.uniqid(),
    ]);

    foreach ($permNames as $permName) {
        $perm = Permission::firstOrCreate(['name' => $permName], ['display_name' => $permName]);
        $role->permissions()->attach($perm->id);
    }

    $user = User::factory()->create(['name' => $name]);
    $user->roles()->attach($role->id);

    return $user;
}

test('observation show view renders patient context header, stage nav, and episode details', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
        'view-observations',
        'manage-observations',
    ], 'dr. Fajar');

    $person = Person::factory()->create(['name' => 'Santri Zaki Ramadhan', 'nis_nip' => 'NIS-2026-044']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'MRN-2026-0044']);
    PatientAllergy::create([
        'patient_id' => $patient->id,
        'allergen' => 'Amoxicillin Trihydrate',
        'reaction' => 'Ruam kemerahan dan gatal',
        'severity' => 'severe',
        'status' => 'confirmed',
        'clinical_status' => 'active',
        'recorded_by_id' => $doctor->id,
    ]);

    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_observation',
        'chief_complaint' => 'Demam tinggi dan menggigil sejak malam',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $episode = ObservationEpisode::create([
        'medical_visit_id' => $visit->id,
        'reason' => 'Observasi demam dan hidrasi',
        'status' => 'active',
        'started_at' => now(),
        'started_by_id' => $doctor->id,
        'responsible_officer_id' => $doctor->id,
        'location_label' => 'Ruang Observasi Putra',
        'bed_label' => 'Bed 02',
        'monitoring_interval_minutes' => 60,
    ]);

    ObservationRecord::create([
        'observation_episode_id' => $episode->id,
        'condition_summary' => 'Suhu 38.2 C, santri telah diberikan kompres hangat',
        'symptom_changes' => 'Menggigil mulai berkurang',
        'general_condition' => 'moderate',
        'recorded_at' => now(),
        'recorded_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('observations.show', $episode->id));

    $response->assertStatus(200);
    $response->assertSee('Santri Zaki Ramadhan');
    $response->assertSee('MRN-2026-0044');
    $response->assertSee('Amoxicillin Trihydrate');
    $response->assertSee('Episode Observasi Rawat Inap Poskestren');
    $response->assertSee('Ruang Observasi Putra');
    $response->assertSee('Bed 02');
    $response->assertSee('Suhu 38.2 C, santri telah diberikan kompres hangat');
    $response->assertSee('Handover Shift');
    $response->assertSee('Selesaikan Observasi');
});

test('completed observation episode renders locked notice and hides new monitoring form', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
        'view-observations',
    ]);

    $person = Person::factory()->create(['name' => 'Santri Umar']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_observation',
        'chief_complaint' => 'Observasi pusing',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $episode = ObservationEpisode::create([
        'medical_visit_id' => $visit->id,
        'reason' => 'Observasi pusing',
        'status' => 'completed',
        'started_at' => now()->subHours(4),
        'ended_at' => now(),
        'started_by_id' => $doctor->id,
        'responsible_officer_id' => $doctor->id,
        'location_label' => 'Ruang Observasi Putri',
        'bed_label' => 'Bed 01',
        'outcome' => 'return_to_activity_recommended',
        'outcome_reason' => 'Kondisi stabil setelah istirahat cukup',
    ]);

    $response = $this->actingAs($doctor)->get(route('observations.show', $episode->id));

    $response->assertStatus(200);
    $response->assertSee('Observasi Telah Ditutup');
    $response->assertSee('Observasi Selesai (Read-Only)');
    $response->assertDontSee('Tambah Lembar Monitoring Berkala');
});

test('consultation show view clearly differentiates external advice from local clinical decisions', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
        'view-clinical-consultations',
        'send-clinical-consultations',
    ]);

    $partner = HealthcarePartner::create([
        'code' => 'MITRA-'.uniqid(),
        'name' => 'Puskesmas Cipayung',
        'partner_type' => 'puskesmas',
        'status' => 'active',
    ]);
    $person = Person::factory()->create(['name' => 'Santri Hasan']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'external_consultation_pending',
        'chief_complaint' => 'Konsultasi antibiotik',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'patient_id' => $patient->id,
        'assessed_by_id' => $doctor->id,
        'chief_complaint' => 'Konsultasi antibiotik',
        'history_current_illness' => 'Demam dan radang tenggorokan',
        'relevant_history' => 'Alergi ringan amoxicillin',
        'examination_findings' => 'Faring hiperemis',
        'assessment_summary' => 'Faringitis Akut',
        'working_diagnosis' => 'Faringitis Akut',
        'disposition_recommendation' => 'continue_poskestren_care',
        'status' => 'finalized',
    ]);

    $consultation = ClinicalConsultation::create([
        'medical_visit_id' => $visit->id,
        'clinical_assessment_id' => $assessment->id,
        'healthcare_partner_id' => $partner->id,
        'purpose' => 'Konsultasi Dosis Antibiotik Lanjutan',
        'clinical_question' => 'Apakah amoxicillin dapat diganti dengan cefixime untuk riwayat alergi ringan?',
        'urgency' => 'urgent',
        'status' => 'responded',
        'created_by_id' => $doctor->id,
    ]);

    ExternalClinicalAdvice::create([
        'clinical_consultation_id' => $consultation->id,
        'healthcare_partner_id' => $partner->id,
        'clinician_name' => 'dr. Hendra Sp.A',
        'clinician_profession' => 'Dokter Spesialis Anak',
        'advice_text' => 'Dapat diberikan Cefixime 100mg 2x1 bila tidak ada riwayat anafilaksis',
        'received_at' => now(),
        'recorded_by_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $response = $this->actingAs($doctor)->get(route('consultations.show', $consultation->id));

    $response->assertStatus(200);
    $response->assertSee('Santri Hasan');
    $response->assertSee('Konsultasi Dosis Antibiotik Lanjutan');
    $response->assertSee('LOCAL-DEVELOPMENT / SIMULATED TRANSPORT');
    $response->assertSee('dr. Hendra Sp.A');
    $response->assertSee('Dapat diberikan Cefixime 100mg 2x1');
    $response->assertSee('Prinsip Klinis Konsultasi Jarak Jauh');
    $response->assertSee('Penetapan Keputusan Klinis Lokal Poskestren');

});

test('referral show view displays complete lifecycle stepper and action forms', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
        'view-referrals',
        'manage-referrals',
    ]);

    $partner = HealthcarePartner::create([
        'code' => 'RS-'.uniqid(),
        'name' => 'RSUD Pasar Rebo',
        'partner_type' => 'hospital',
        'status' => 'active',
    ]);
    $person = Person::factory()->create(['name' => 'Santri Fadhil']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_assessment',
        'chief_complaint' => 'Nyeri perut kanan bawah',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'patient_id' => $patient->id,
        'assessed_by_id' => $doctor->id,
        'chief_complaint' => 'Nyeri perut kanan bawah',
        'history_current_illness' => 'Nyeri perut sejak subuh',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'Nyeri tekan McBurney positif',
        'assessment_summary' => 'Suspek Apendisitis Akut',
        'working_diagnosis' => 'Suspek Apendisitis Akut',
        'disposition_recommendation' => 'referral_recommended',
        'status' => 'finalized',
    ]);

    $referral = Referral::create([
        'medical_visit_id' => $visit->id,
        'clinical_assessment_id' => $assessment->id,
        'healthcare_partner_id' => $partner->id,
        'referral_number' => Referral::generateReferralNumber(),
        'reason' => 'Suspek Apendisitis Akut',
        'clinical_summary' => 'Nyeri perut kanan bawah McBurney sign positif',
        'urgency' => 'emergency',
        'status' => 'in_transit',
        'initiated_by_id' => $doctor->id,
    ]);

    ReferralTransport::create([
        'referral_id' => $referral->id,
        'transport_type' => 'ambulance',
        'vehicle_identifier' => 'B 1234 POS',
        'driver_name' => 'Pak Joko',
        'departed_at' => now()->subMinutes(20),
        'status' => 'departed',
        'created_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('referrals.show', $referral->id));

    $response->assertStatus(200);
    $response->assertSee('Santri Fadhil');
    $response->assertSee($referral->referral_number);
    $response->assertSee('RSUD Pasar Rebo');
    $response->assertSee('DARURAT (EMERGENCY)');
    $response->assertSee('1. Disiapkan');
    $response->assertSee('2. Berangkat');
    $response->assertSee('3. Tiba di Faskes');
    $response->assertSee('B 1234 POS');
    $response->assertSee('Ambulance');
});

test('discharge workspace renders patient context header, readiness evaluation, and minimum necessary handoffs', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
        'view-visit-discharges',
        'prepare-visit-discharges',
        'finalize-visit-discharges',
    ]);

    $person = Person::factory()->create(['name' => 'Santriwati Maryam']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_assessment',
        'chief_complaint' => 'Demam dan pusing',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'patient_id' => $patient->id,
        'assessed_by_id' => $doctor->id,
        'chief_complaint' => 'Demam dan pusing',
        'history_current_illness' => 'Demam sejak 2 hari lalu',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'Suhu 37.0 C',
        'assessment_summary' => 'ISPA Ringan dalam perbaikan',
        'working_diagnosis' => 'ISPA Ringan',
        'disposition_recommendation' => 'rest_required',
        'status' => 'finalized',
    ]);

    $discharge = VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'rest_required',
        'discharge_destination' => 'Asrama Khadijah',
        'clinical_summary' => 'Kondisi membaik, demam turun',
        'final_condition' => 'Baik / Stabil',
        'activity_recommendation' => 'Istirahat di kamar asrama selama 2 hari',
        'follow_up_required' => true,
        'follow_up_summary' => 'Kontrol ulang ke Poskestren bila demam berulang',
        'follow_up_date' => now()->addDays(2),
        'prepared_by_id' => $doctor->id,
        'prepared_at' => now(),
        'status' => 'draft',
    ]);

    VisitFollowUpPlan::create([
        'visit_discharge_id' => $discharge->id,
        'follow_up_type' => 'routine_recheck',
        'due_at' => now()->addDays(2),
        'instructions' => 'Evaluasi suhu dan nafsu makan',
        'status' => 'scheduled',
        'created_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('visits.discharge', $visit->id));

    $response->assertStatus(200);
    $response->assertSee('Santriwati Maryam');
    $response->assertSee('Kepulangan Klinis & Penutupan Kunjungan', false);
    $response->assertSee('Asrama Khadijah');
    $response->assertSee('Finalisasi & Tutup Kunjungan', false);

});

test('visit overview displays dynamic next action guidance and all module cards', function () {
    $doctor = makePhase5BUser([
        'view-medical-visits',
    ]);

    $person = Person::factory()->create(['name' => 'Santri Bilal']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_assessment',
        'chief_complaint' => 'Batuk berdahak 3 hari',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    VitalSign::create([
        'medical_visit_id' => $visit->id,
        'systolic_bp' => 110,
        'diastolic_bp' => 70,
        'temperature_c' => 36.8,
        'pulse_bpm' => 78,
        'spo2_percent' => 99,
        'status' => 'finalized',
        'recorded_at' => now(),
        'recorded_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('visits.show', $visit->id));

    $response->assertStatus(200);
    $response->assertSee('1. Pemeriksaan Tanda Vital');
    $response->assertSee('2. Pengkajian Medis & SOAP', false);
    $response->assertSee('3. Ruang Observasi Rawat Inap');
    $response->assertSee('4. Tele-Konsultasi Eksternal');
    $response->assertSee('5. Rujukan RS / Faskes Lanjutan');
    $response->assertSee('6. Resep & Dispensing Obat', false);
    $response->assertSee('7. Disposisi & Kepulangan Medis', false);
    $response->assertSee('110/70');
    $response->assertSee('36.8');
    $response->assertSee('Lanjutkan ke pengisian anamnesis dan impresi diagnostik pada formulir SOAP');
});

test('pharmacy inventory view displays batch statuses and flags expired batches', function () {
    $pharmacist = makePhase5BUser([
        'view-pharmacy-inventory',
    ], 'Apoteker POSKESTREN');

    $location = StockLocation::create([
        'code' => 'APTK-01',
        'name' => 'Apotek Utama Poskestren',
        'type' => 'internal_pharmacy',
    ]);

    $medicine = Medicine::create([
        'code' => 'MED-'.uniqid(),
        'generic_name' => 'Paracetamol Drops 100mg/ml',
        'brand_name' => 'Paracetamol Drops',
        'dosage_form' => 'drops',
        'strength_text' => '100mg/ml',
        'base_unit' => 'botol',
        'category' => 'Analgesik',
        'is_active' => true,
    ]);

    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-EXP-001',
        'expiry_date' => now()->subDays(10), // Expired
        'received_at' => now()->subMonths(6),
        'initial_quantity' => 20,
        'current_quantity' => 15,
        'status' => 'expired',
        'created_by_id' => $pharmacist->id,
    ]);

    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-ACT-002',
        'expiry_date' => now()->addMonths(12), // Good
        'received_at' => now()->subMonth(),
        'initial_quantity' => 50,
        'current_quantity' => 45,
        'status' => 'active',
        'created_by_id' => $pharmacist->id,
    ]);

    $response = $this->actingAs($pharmacist)->get(route('pharmacy.inventory.index'));

    $response->assertStatus(200);
    $response->assertSee('Stok & Batch Inventaris Farmasi', false);
    $response->assertSee('Paracetamol Drops 100mg/ml');
    $response->assertSee('BATCH-EXP-001');
    $response->assertSee('BATCH-ACT-002');
    $response->assertSee('Kedaluwarsa');
    $response->assertSee('15 botol');
    $response->assertSee('45 botol');
});

test('pharmacy expiry warning threshold is configurable via pharmacy config', function () {
    config(['pharmacy.expiry_warning_days' => 60]);

    $batch = new MedicineBatch;
    $batch->expiry_date = now()->addDays(45);

    expect($batch->isNearExpiry())->toBeTrue();

    config(['pharmacy.expiry_warning_days' => 30]);
    expect($batch->isNearExpiry())->toBeFalse();
});
