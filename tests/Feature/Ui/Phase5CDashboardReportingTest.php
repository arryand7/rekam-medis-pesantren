<?php

namespace Tests\Feature\Ui;

use App\Models\MedicalVisit;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\VisitDischarge;
use Illuminate\Support\Facades\DB;

function createPhase5cUserWithPermissions(array $permissions, string $name = 'Test User'): User
{
    $user = User::factory()->create(['name' => $name]);
    $role = Role::firstOrCreate(['name' => 'role_'.md5(implode('_', $permissions))], ['display_name' => 'Test Role']);

    $permIds = [];
    foreach ($permissions as $permName) {
        $perm = Permission::firstOrCreate(['name' => $permName], ['display_name' => $permName]);
        $permIds[] = $perm->id;
    }

    $role->permissions()->syncWithoutDetaching($permIds);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

test('clinical dashboard renders successfully with metrics and actionable work queues', function () {
    $doctor = createPhase5cUserWithPermissions(['view-clinical-dashboard', 'view-medical-visits'], 'dr. Faisal Medis');

    $person = Person::factory()->create(['name' => 'Ahmad Santri']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'RM-202608-0099', 'is_eligible' => true]);

    // 1. Visit waiting assessment
    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VISIT-CLIN-001',
        'status' => 'waiting_assessment',
        'chief_complaint' => 'Demam tinggi menggigil',
        'created_by_id' => $doctor->id,
        'created_at' => now()->subMinutes(20),
    ]);

    // 2. Active Observation
    $obs = ObservationEpisode::create([
        'medical_visit_id' => $visit->id,
        'status' => 'active',
        'bed_label' => 'Bed 01 Isolasi',
        'reason' => 'Observasi demam akut',
        'monitoring_interval_minutes' => 30,
        'next_monitoring_due_at' => now()->addMinutes(15),
        'started_at' => now()->subHour(),
        'responsible_officer_id' => $doctor->id,
        'created_by_id' => $doctor->id,
    ]);

    $response = $this->actingAs($doctor)->get(route('dashboards.clinical'));

    $response->assertOk();
    $response->assertSee('Dashboard Klinis', false);
    $response->assertSee('Ahmad Santri');
    $response->assertSee('RM: RM-202608-0099');
    $response->assertSee('Demam tinggi menggigil');
    $response->assertSee('Bed 01 Isolasi');
    $response->assertSee('Mulai Periksa &rarr;', false);
});

test('operational dashboard strictly adheres to minimum-necessary privacy without clinical leak', function () {
    $musyrif = createPhase5cUserWithPermissions(['view-operational-dashboard'], 'Ustadz Asrama');

    $person = Person::factory()->create(['name' => 'Fulan Santriwati']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);

    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VISIT-OPS-001',
        'status' => 'completed',
        'chief_complaint' => 'Rahasia Diagnosa Medis Demam Tifoid Akut',
        'created_by_id' => $musyrif->id,
        'created_at' => now(),
    ]);

    // Discharge with activity recommendation
    VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'regular',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Sangat rahasia: pasien terinfeksi Salmonella typhi SOAP detail',
        'final_condition' => 'Membaik',
        'activity_recommendation' => 'bed_rest',
        'rest_recommendation' => 'istirahat_asrama_3_hari',
        'restriction_notes' => 'Tidur di kamar santri, dilarang piket dan shalat berjamaah di masjid',
        'follow_up_required' => false,
        'prepared_by_id' => $musyrif->id,
        'prepared_at' => now(),
        'status' => 'finalized',
        'finalized_at' => now(),
        'finalized_by_id' => $musyrif->id,
    ]);

    $response = $this->actingAs($musyrif)->get(route('dashboards.operational'));

    $response->assertOk();
    $response->assertSee('Dashboard Operasional Asrama', false);
    $response->assertSee('Fulan Santriwati');
    $response->assertSee('Tidur di kamar santri, dilarang piket dan shalat berjamaah di masjid');

    // PRIVACY ENFORCEMENT: Assert absence of sensitive clinical narrative
    $response->assertDontSee('Salmonella typhi');
    $response->assertDontSee('Demam Tifoid Akut');
    $response->assertDontSee('SOAP detail');
});

test('operational role cannot access clinical dashboard or clinical visits directly', function () {
    $musyrif = createPhase5cUserWithPermissions(['view-operational-dashboard'], 'Ustadz Asrama');

    $this->actingAs($musyrif)->get(route('dashboards.clinical'))->assertForbidden();
    $this->actingAs($musyrif)->get(route('visits.index'))->assertForbidden();
    $this->actingAs($musyrif)->get(route('referrals.index'))->assertForbidden();
});

test('pharmacy dashboard calculates expired batches and near expiry threshold correctly', function () {
    $pharmacist = createPhase5cUserWithPermissions(['view-pharmacy-dashboard', 'view-pharmacy-inventory'], 'Apoteker Poskestren');

    $location = StockLocation::firstOrCreate(['code' => 'PHARM_TEST'], ['name' => 'Gudang Farmasi Test', 'is_active' => true]);
    $medicine = Medicine::firstOrCreate(['code' => 'MED-TEST-01'], [
        'generic_name' => 'Paracetamol',
        'brand_name' => 'Paracetamol 500mg Test',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
        'is_active' => true,
    ]);

    // 1. Expired batch with stock
    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-EXP-001',
        'expiry_date' => now()->subDays(5)->toDateString(),
        'initial_quantity' => 50,
        'current_quantity' => 30,
        'is_active' => true,
    ]);

    // 2. Near expiry batch (within 10 days)
    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-NEAR-002',
        'expiry_date' => now()->addDays(10)->toDateString(),
        'initial_quantity' => 100,
        'current_quantity' => 80,
        'is_active' => true,
    ]);

    // 3. Depleted batch
    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-DEP-003',
        'expiry_date' => now()->addDays(100)->toDateString(),
        'initial_quantity' => 50,
        'current_quantity' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($pharmacist)->get(route('dashboards.pharmacy'));

    $response->assertOk();
    $response->assertSee('Dashboard Farmasi', false);
    $response->assertSee('BATCH-EXP-001');
    $response->assertSee('EXPIRED');
    $response->assertSee('BATCH-NEAR-002');
    $response->assertSee('BATCH-DEP-003');
});

test('management dashboard displays aggregate numbers, date presets, and enforces zero PII', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    $person = Person::factory()->create(['name' => 'SensitivePatientNameUnique99']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'RM-SECRET-777', 'is_eligible' => true]);

    $officer = User::factory()->create();

    MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VISIT-AGG-001',
        'status' => 'completed',
        'chief_complaint' => 'Keluhan rahasia individual',
        'created_by_id' => $officer->id,
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($director)->get(route('dashboards.management', ['preset' => '7_days']));

    $response->assertOk();
    $response->assertSee('Dashboard Manajemen Eksekutif');
    $response->assertSee('Total Kunjungan Medis');
    $response->assertSee('Pasien Unik Dilayani');

    // PRIVACY ENFORCEMENT: Assert absence of individual patient name & MRN
    $response->assertDontSee('SensitivePatientNameUnique99');
    $response->assertDontSee('RM-SECRET-777');
});

test('technical admin without management permission cannot access management dashboard', function () {
    $admin = createPhase5cUserWithPermissions(['manage-users', 'view-people'], 'Admin Teknis');

    $this->actingAs($admin)->get(route('dashboards.management'))->assertForbidden();
});

test('reports show renders with KPI summary and streaming CSV export works with metadata', function () {
    $auditor = createPhase5cUserWithPermissions(['view-health-reports', 'export-health-reports'], 'Kepala Pengawas Medis');

    $person = Person::factory()->create(['name' => 'Zaid Santri']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'RM-EXPORT-123', 'is_eligible' => true]);

    MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VISIT-EXP-001',
        'status' => 'registered',
        'chief_complaint' => 'Batuk pilek 2 hari',
        'created_by_id' => $auditor->id,
        'created_at' => now(),
    ]);

    // 1. Report View
    $viewResponse = $this->actingAs($auditor)->get(route('reports.show', ['report_type' => 'visit_census']));
    $viewResponse->assertOk();
    $viewResponse->assertSee('VISIT-EXP-001');
    $viewResponse->assertSee('Ekspor ke CSV (Excel)');

    // 2. Export Stream
    $exportResponse = $this->actingAs($auditor)->get(route('reports.export', ['report_type' => 'visit_census']));
    $exportResponse->assertOk();
    $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // Capture streamed content
    ob_start();
    $exportResponse->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('# LAPORAN POSKESTREN SABIRA HEALTH');
    expect($csvContent)->toContain('VISIT-EXP-001');
    expect($csvContent)->toContain('RM-EXPORT-123');
});

test('dashboard queries execute efficiently within upper bound query count', function () {
    $doctor = createPhase5cUserWithPermissions(['view-clinical-dashboard', 'view-medical-visits'], 'dr. Sp.PD');

    DB::enableQueryLog();

    $response = $this->actingAs($doctor)->get(route('dashboards.clinical'));

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();
    expect($queryCount)->toBeLessThan(50);
});
