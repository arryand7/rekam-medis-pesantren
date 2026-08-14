<?php

namespace Tests\Feature\Ui;

use App\Models\IntegrationDeliveryAttempt;
use App\Models\IntegrationOutboxEvent;
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
use App\Models\VisitFollowUpPlan;
use App\Queries\Dashboard\ManagementDashboardQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    ]);
    $visit->created_at = now()->subMinutes(20);
    $visit->save();

    // 2. Active Observation
    ObservationEpisode::create([
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
    ]);

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

    config(['pharmacy.expiry_warning_days' => 30]);

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
    $response->assertSee('Kedaluwarsa');
    $response->assertSee('BATCH-NEAR-002');
    $response->assertSee('BATCH-DEP-003');
});

test('pharmacy dashboard handles unconfigured low stock threshold safely', function () {
    $pharmacist = createPhase5cUserWithPermissions(['view-pharmacy-dashboard', 'view-pharmacy-inventory'], 'Apoteker Poskestren');

    config(['pharmacy.low_stock_threshold' => null]);

    $response = $this->actingAs($pharmacist)->get(route('dashboards.pharmacy'));

    $response->assertOk();
    $response->assertSee('Belum Dikonfigurasi');
    $response->assertSee('[PERLU DIKONFIRMASI]');
});

test('management dashboard displays aggregate numbers, date presets, and enforces zero PII', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    $person = Person::factory()->create(['name' => 'SensitivePatientNameUnique99']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => 'RM-SECRET-777', 'is_eligible' => true]);

    $officer = User::factory()->create();

    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VISIT-AGG-001',
        'status' => 'completed',
        'chief_complaint' => 'Keluhan rahasia individual',
        'created_by_id' => $officer->id,
    ]);
    $visit->created_at = now()->subDays(2);
    $visit->save();

    $response = $this->actingAs($director)->get(route('dashboards.management', ['preset' => '7_days']));

    $response->assertOk();
    $response->assertSee('Dashboard Manajemen Eksekutif');
    $response->assertSee('Total Kunjungan Medis');
    $response->assertSee('Pasien Unik Dilayani');

    // PRIVACY ENFORCEMENT: Assert absence of individual patient name & MRN
    $response->assertDontSee('SensitivePatientNameUnique99');
    $response->assertDontSee('RM-SECRET-777');
    $response->assertDontSee('Keluhan rahasia individual');
});

test('management dashboard handles zero denominator follow up without fake 100 percent', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    // Ensure zero follow up records in the database for the period
    VisitFollowUpPlan::query()->delete();

    $response = $this->actingAs($director)->get(route('dashboards.management', ['preset' => 'today']));

    $response->assertOk();
    $response->assertSee('Belum ada data');
    $response->assertDontSee('100%');
});

test('management dashboard validates custom date range input strictly', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    // 1. Valid custom date range
    $validResponse = $this->actingAs($director)->get(route('dashboards.management', [
        'preset' => 'custom',
        'from' => now()->subDays(5)->toDateString(),
        'to' => now()->toDateString(),
    ]));
    $validResponse->assertOk();

    // 2. Invalid date order (from > to)
    $invalidResponse = $this->actingAs($director)->get(route('dashboards.management', [
        'preset' => 'custom',
        'from' => now()->toDateString(),
        'to' => now()->subDays(5)->toDateString(),
    ]));
    $invalidResponse->assertSessionHasErrors(['to']);

    // 3. Unknown preset
    $badPresetResponse = $this->actingAs($director)->get(route('dashboards.management', [
        'preset' => 'invalid_preset_foo',
    ]));
    $badPresetResponse->assertSessionHasErrors(['preset']);
});

test('management query count is constant and does not scale linearly with number of days', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    $query = new ManagementDashboardQuery;

    DB::enableQueryLog();

    // 1. Direct query evaluation
    $metrics = $query->getMetrics(now()->subDays(29), now());

    $directQueryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($directQueryCount)->toBeLessThanOrEqual(18);
    expect($metrics['daily_trends'])->toHaveCount(30);

    // 2. Full HTTP request response
    $response = $this->actingAs($director)->get(route('dashboards.management', ['preset' => '30_days']));
    $response->assertOk();
});

test('technical admin without management permission cannot access management dashboard', function () {
    $admin = createPhase5cUserWithPermissions(['manage-users', 'view-people'], 'Admin Teknis');

    $this->actingAs($admin)->get(route('dashboards.management'))->assertForbidden();
});

test('management user cannot access patient level reports or export without explicit reporting permission', function () {
    $director = createPhase5cUserWithPermissions(['view-management-dashboard'], 'Direktur Pesantren');

    // Denied from reports center
    $this->actingAs($director)->get(route('reports.index'))->assertForbidden();
    $this->actingAs($director)->get(route('reports.show', ['report_type' => 'visit_census']))->assertForbidden();
    $this->actingAs($director)->get(route('reports.export', ['report_type' => 'visit_census']))->assertForbidden();
});

test('report summary KPI strictly respects date range and status filters', function () {
    $auditor = createPhase5cUserWithPermissions(['view-health-reports', 'export-health-reports'], 'Auditor Medis');

    $person1 = Person::factory()->create(['name' => 'Pasien Kemarin']);
    $patient1 = Patient::factory()->create(['person_id' => $person1->id]);

    $person2 = Person::factory()->create(['name' => 'Pasien Minggu Lalu']);
    $patient2 = Patient::factory()->create(['person_id' => $person2->id]);

    // Visit inside filter window (today)
    $visit1 = MedicalVisit::create([
        'patient_id' => $patient1->id,
        'visit_number' => 'VISIT-TODAY-001',
        'status' => 'completed',
        'chief_complaint' => 'Sakit kepala',
        'created_by_id' => $auditor->id,
    ]);
    $visit1->created_at = now()->startOfDay()->addHours(2);
    $visit1->save();

    // Visit outside filter window (10 days ago)
    $visit2 = MedicalVisit::create([
        'patient_id' => $patient2->id,
        'visit_number' => 'VISIT-OLD-002',
        'status' => 'completed',
        'chief_complaint' => 'Sakit perut',
        'created_by_id' => $auditor->id,
    ]);
    $visit2->created_at = now()->subDays(10);
    $visit2->save();

    // 1. Filter by start_date = today: only 1 visit should be in KPI & Table
    $response = $this->actingAs($auditor)->get(route('reports.show', [
        'report_type' => 'visit_census',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertSee('VISIT-TODAY-001');
    $response->assertDontSee('VISIT-OLD-002');

    // 2. Filter by status = waiting_assessment: total completed should be 0
    $statusResponse = $this->actingAs($auditor)->get(route('reports.show', [
        'report_type' => 'visit_census',
        'status' => 'waiting_assessment',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $statusResponse->assertOk();
    $statusResponse->assertDontSee('VISIT-TODAY-001');
});

test('export health report protects against CSV formula injection', function () {
    $auditor = createPhase5cUserWithPermissions(['view-health-reports', 'export-health-reports'], 'Petugas Medis');

    $person = Person::factory()->create(['name' => '=HYPERLINK("http://attacker.test","Klik Disini")']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'patient_number' => '+6281234567']);

    MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => '@SUM(1+1)',
        'status' => '-DANGEROUS',
        'chief_complaint' => '=cmd|"/C calc"!A0',
        'created_by_id' => $auditor->id,
    ]);

    $response = $this->actingAs($auditor)->get(route('reports.export', ['report_type' => 'visit_census']));
    $response->assertOk();

    ob_start();
    $response->sendContent();
    $csvContent = (string) ob_get_clean();

    // Verify formula characters are neutralized with leading single quote
    expect($csvContent)->toContain("'=HYPERLINK");
    expect($csvContent)->toContain("'+6281234567");
    expect($csvContent)->toContain("'@SUM");
    expect($csvContent)->toContain("'-DANGEROUS");
    expect($csvContent)->toContain("'=cmd");
});

test('export rejects unknown report types with validation error', function () {
    $auditor = createPhase5cUserWithPermissions(['view-health-reports', 'export-health-reports'], 'Petugas Medis');

    $response = $this->actingAs($auditor)->get(route('reports.export', ['report_type' => 'hacked_unknown_type']));
    $response->assertSessionHasErrors(['report_type']);
});

test('integration delivery report exports dedicated integration columns without leaking patient visit census', function () {
    $auditor = createPhase5cUserWithPermissions(['view-health-reports', 'export-health-reports'], 'Petugas Medis');

    $outbox = IntegrationOutboxEvent::create([
        'event_type' => 'health.visit.recorded',
        'aggregate_type' => 'MedicalVisit',
        'aggregate_id' => (string) Str::ulid(),
        'destination' => 'attendance_sandbox',
        'payload_snapshot' => ['visit_number' => 'VISIT-TEST'],
        'payload_version' => 1,
        'idempotency_key' => (string) Str::uuid(),
        'status' => 'pending',
        'available_at' => now(),
        'attempt_count' => 1,
        'correlation_id' => (string) Str::uuid(),
    ]);

    IntegrationDeliveryAttempt::create([
        'outbox_event_id' => $outbox->id,
        'attempt_number' => 1,
        'destination' => 'attendance_sandbox',
        'started_at' => now(),
        'result' => 'success',
        'http_status_code' => 200,
        'latency_ms' => 45,
        'correlation_id' => (string) Str::uuid(),
    ]);

    $response = $this->actingAs($auditor)->get(route('reports.export', ['report_type' => 'integration_delivery']));
    $response->assertOk();

    ob_start();
    $response->sendContent();
    $csvContent = (string) ob_get_clean();

    expect($csvContent)->toContain('ID Pengiriman');
    expect($csvContent)->toContain('attendance_sandbox');
    expect($csvContent)->toContain('Kode HTTP');
    expect($csvContent)->not->toContain('Nomor Kunjungan');
    expect($csvContent)->not->toContain('Nama Pasien');
});
