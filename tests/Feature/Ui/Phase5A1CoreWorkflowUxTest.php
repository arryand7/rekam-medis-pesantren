<?php

use App\Models\ClinicalAssessment;
use App\Models\MedicalVisit;
use App\Models\MedicationOrder;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;

function makeUiTestUser(array $permNames, string $name = 'Test User'): User
{
    $role = Role::create([
        'name' => 'ui_role_'.uniqid(),
        'display_name' => 'UI Role '.uniqid(),
    ]);

    foreach ($permNames as $permName) {
        $perm = Permission::firstOrCreate(['name' => $permName], ['display_name' => $permName]);
        $role->permissions()->attach($perm->id);
    }

    $user = User::factory()->create(['name' => $name]);
    $user->roles()->attach($role->id);

    return $user;
}

test('app layout renders user info and logout button for authenticated user', function () {
    $user = makeUiTestUser(['view-clinical-dashboard', 'view-medical-visits'], 'dr. Zaid Husain');

    $response = $this->actingAs($user)->get(route('dashboards.clinical'));

    $response->assertStatus(200);
    $response->assertSee('dr. Zaid Husain');
    $response->assertSee('Keluar');
});

test('clinical staff sees clinical navigation menus in sidebar', function () {
    $doctor = makeUiTestUser([
        'view-clinical-dashboard',
        'view-medical-visits',
        'view-patients',
        'create-clinical-assessments',
    ], 'dr. Aisyah');

    $response = $this->actingAs($doctor)->get(route('visits.index'));

    $response->assertStatus(200);
    $response->assertSee('Pelayanan Medis');
    $response->assertSee('Kunjungan (Intake)');
    $response->assertSee('Data Rekam Medis');
});

test('technical admin does not see clinical navigation menus in sidebar', function () {
    $admin = makeUiTestUser(['manage-users'], 'IT SysAdmin');

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee('Pelayanan Medis');
    $response->assertDontSee('Kunjungan (Intake)');
    $response->assertSee('Administrasi & Sistem', false);
    $response->assertSee('Akun Pengguna');

});

test('unauthorized user accessing clinical route directly is denied with 403', function () {
    $admin = makeUiTestUser(['manage-users'], 'IT Admin Only');

    $response = $this->actingAs($admin)->get(route('visits.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($admin)->get(route('patients.index'));
    $response->assertStatus(403);
});

test('patient index renders search input and lists patients', function () {
    $doctor = makeUiTestUser(['view-patients']);

    $person = Person::factory()->create(['name' => 'Santri Muhammad Farhan', 'nis_nip' => 'NIS-2026-001']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'MRN-2026-0001']);

    $response = $this->actingAs($doctor)->get(route('patients.index'));

    $response->assertStatus(200);
    $response->assertSee('Direktori Rekam Medis Pasien');
    $response->assertSee('Santri Muhammad Farhan');
    $response->assertSee('MRN-2026-0001');
    $response->assertSee('Cari Pasien');
});

test('patient search filters correctly by name and MRN', function () {
    $doctor = makeUiTestUser(['view-patients']);

    $person1 = Person::factory()->create(['name' => 'Ahmad Dahlan', 'nis_nip' => 'NIS-001']);
    $patient1 = Patient::factory()->create(['person_id' => $person1->id, 'patient_number' => 'MRN-1001']);

    $person2 = Person::factory()->create(['name' => 'Budi Santoso', 'nis_nip' => 'NIS-002']);
    $patient2 = Patient::factory()->create(['person_id' => $person2->id, 'patient_number' => 'MRN-1002']);

    $response = $this->actingAs($doctor)->get(route('patients.index', ['search' => 'Ahmad']));
    $response->assertStatus(200);
    $response->assertSee('Ahmad Dahlan');
    $response->assertDontSee('Budi Santoso');

    $responseMRN = $this->actingAs($doctor)->get(route('patients.index', ['search' => 'MRN-1002']));
    $responseMRN->assertStatus(200);
    $responseMRN->assertSee('Budi Santoso');
    $responseMRN->assertDontSee('Ahmad Dahlan');
});

test('patient search with no matching result displays graceful empty state', function () {
    $doctor = makeUiTestUser(['view-patients']);

    $response = $this->actingAs($doctor)->get(route('patients.index', ['search' => 'NamaTidakMungkinAda12345']));

    $response->assertStatus(200);
    $response->assertSee('Data Pasien Tidak Ditemukan');
    $response->assertSee('Reset Pencarian');
});

test('patient context header renders patient information and allergy indicator', function () {
    $doctor = makeUiTestUser(['view-patients']);

    $person = Person::factory()->create(['name' => 'Fulan bin Fulan', 'nis_nip' => 'NIS-888', 'gender' => 'L']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'MRN-8888', 'is_eligible' => true]);

    PatientAllergy::create([
        'patient_id' => $patient->id,
        'allergen' => 'Amoxicillin',
        'reaction' => 'Bintik merah dan gatal',
        'severity' => 'severe',
        'clinical_status' => 'active',
        'verification_status' => 'confirmed',
    ]);

    $response = $this->actingAs($doctor)->get(route('patients.show', $patient->id));

    $response->assertStatus(200);
    $response->assertSee('Fulan bin Fulan');
    $response->assertSee('MRN-8888');
    $response->assertSee('PERINGATAN ALERGI AKTIF');
    $response->assertSee('Amoxicillin');
});

test('visit workspace show renders patient context header, stage navigation, and clinical cards', function () {
    $doctor = makeUiTestUser(['view-medical-visits', 'view-patients', 'view-medication-orders']);

    $person = Person::factory()->create(['name' => 'Santri Abdullah']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam tinggi 3 hari dan batuk berdahak',
        'status' => 'waiting_assessment',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    VitalSign::create([
        'medical_visit_id' => $visit->id,
        'patient_id' => $patient->id,
        'recorded_by_id' => $doctor->id,
        'systolic_bp' => 110,
        'diastolic_bp' => 70,
        'temperature_c' => 38.5,
        'pulse_bpm' => 88,
        'spo2_percent' => 99,
        'status' => 'finalized',
    ]);

    ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Pasien mengeluh demam sejak 3 hari lalu.',
        'relevant_history' => 'Tidak ada riwayat alergi.',
        'examination_findings' => 'Suhu 38.5C, faring hiperemis.',
        'assessment_summary' => 'Faringitis akut dengan gejala demam dan faring hiperemis.',
        'working_diagnosis' => 'Faringitis Akut',
        'disposition_recommendation' => 'return_to_activity',
        'status' => 'finalized',
    ]);

    $medicine = Medicine::create([
        'code' => 'MED-'.uniqid(),
        'generic_name' => 'Paracetamol',
        'brand_name' => 'Paracetamol 500mg',
        'dosage_form' => 'tablet',
        'strength_text' => '500mg',
        'base_unit' => 'tablet',
        'category' => 'Analgesik',
        'is_active' => true,
    ]);

    MedicationOrder::create([
        'medical_visit_id' => $visit->id,
        'patient_id' => $patient->id,
        'medicine_id' => $medicine->id,
        'ordered_by_id' => $doctor->id,
        'dosage_instruction' => '3x1 tablet sesudah makan',
        'dose_value' => '500',
        'dose_unit' => 'mg',
        'frequency_text' => '3x1 sehari sesudah makan',
        'quantity_prescribed' => 10,
        'status' => 'prescribed',
    ]);

    $response = $this->actingAs($doctor)->get(route('visits.show', $visit->id));

    $response->assertStatus(200);
    // Patient Context Header
    $response->assertSee('Santri Abdullah');
    $response->assertSee($visit->visit_number);
    // Stage Navigation
    $response->assertSee('Ringkasan Kunjungan');
    $response->assertSee('Tanda Vital &amp; SOAP', false);
    $response->assertSee('Resep &amp; Obat', false);
    $response->assertSee('Kepulangan &amp; Handoff', false);
    // Clinical Cards
    $response->assertSee('Demam tinggi 3 hari dan batuk berdahak');
    $response->assertSee('38.5');
    $response->assertSee('Faringitis Akut');
    $response->assertSee('Paracetamol 500mg');
});

test('visit workspace assessment renders stage navigation and SOAP form', function () {
    $doctor = makeUiTestUser(['create-clinical-assessments', 'view-medical-visits']);

    $person = Person::factory()->create(['name' => 'Santri Umar']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_assessment',
        'chief_complaint' => 'Sakit kepala',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('visits.assessment', $visit->id));

    $response->assertStatus(200);
    $response->assertSee('Santri Umar');
    $response->assertSee('Tanda Vital &amp; SOAP', false);
    $response->assertSee('Pemeriksaan Tanda Vital');
    $response->assertSee('Pengkajian Klinis Medis (Assessment)');
});

test('visit workspace medications renders stage navigation and medication management', function () {
    $doctor = makeUiTestUser(['view-medication-orders', 'view-medical-visits']);

    $person = Person::factory()->create(['name' => 'Santri Ali']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => MedicalVisit::generateVisitNumber(),
        'patient_id' => $patient->id,
        'status' => 'under_assessment',
        'chief_complaint' => 'Batuk pilek',
        'arrived_at' => now(),
        'created_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('visits.medications.index', $visit->id));

    $response->assertStatus(200);
    $response->assertSee('Santri Ali');
    $response->assertSee('Resep &amp; Obat', false);
    $response->assertSee('Buat Instruksi Obat Baru');
});
