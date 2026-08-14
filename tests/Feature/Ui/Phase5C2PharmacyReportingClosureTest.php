<?php

namespace Tests\Feature\Ui;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Queries\Dashboard\ManagementDashboardQuery;
use App\Queries\Dashboard\PharmacyDashboardQuery;
use App\Services\Reporting\HealthReportService;

function createPhase5c2UserWithPermissions(array $permissions, string $name = 'Phase 5C2 Test User'): User
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

test('expired and near-expiry batches are strictly mutually exclusive across models, queries, and reports', function () {
    // Clear batches to test with exact dataset
    MedicineBatch::query()->delete();

    config(['pharmacy.expiry_warning_days' => 30]);

    $location = StockLocation::firstOrCreate(['code' => 'PHARM_TEST_5C2'], ['name' => 'Gudang Farmasi 5C2', 'is_active' => true]);
    $medicine = Medicine::firstOrCreate(['code' => 'MED-TEST-5C2'], [
        'generic_name' => 'Amoxicillin',
        'brand_name' => 'Amoxicillin 500mg',
        'dosage_form' => 'kapsul',
        'base_unit' => 'kapsul',
        'is_active' => true,
    ]);

    // Batch A: Expired yesterday, qty > 0 -> expired=YES, near_expiry=NO, normal=NO, depleted=NO
    $batchA = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-A-EXP',
        'expiry_date' => now()->subDay()->toDateString(),
        'initial_quantity' => 50,
        'current_quantity' => 20,
        'status' => 'active',
    ]);

    // Batch B: Expiring in 5 days, qty > 0 -> expired=NO, near_expiry=YES, normal=NO, depleted=NO
    $batchB = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-B-NEAR',
        'expiry_date' => now()->addDays(5)->toDateString(),
        'initial_quantity' => 100,
        'current_quantity' => 80,
        'status' => 'active',
    ]);

    // Batch C: Expiring in 45 days (outside 30-day window), qty > 0 -> expired=NO, near_expiry=NO, normal=YES, depleted=NO
    $batchC = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-C-NORM',
        'expiry_date' => now()->addDays(45)->toDateString(),
        'initial_quantity' => 100,
        'current_quantity' => 100,
        'status' => 'active',
    ]);

    // Batch D: Expired yesterday, but depleted (qty = 0) -> depleted=YES, expired alert count=NO
    $batchD = MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-D-DEP',
        'expiry_date' => now()->subDay()->toDateString(),
        'initial_quantity' => 50,
        'current_quantity' => 0,
        'status' => 'depleted',
    ]);

    // 1. Assert Model Scopes & Instance Methods
    expect($batchA->isExpired())->toBeTrue();
    expect($batchA->isNearExpiry())->toBeFalse();

    expect($batchB->isExpired())->toBeFalse();
    expect($batchB->isNearExpiry())->toBeTrue();

    expect($batchC->isExpired())->toBeFalse();
    expect($batchC->isNearExpiry())->toBeFalse();

    expect(MedicineBatch::expired()->count())->toBe(1);
    expect(MedicineBatch::nearExpiry()->count())->toBe(1);
    expect(MedicineBatch::normal()->count())->toBe(1);
    expect(MedicineBatch::depleted()->count())->toBe(1);

    // 2. Assert PharmacyDashboardQuery KPI Metrics
    $pharmacyQuery = new PharmacyDashboardQuery;
    $pharmacyMetrics = $pharmacyQuery->getMetrics();

    expect($pharmacyMetrics['expired_batches'])->toBe(1);
    expect($pharmacyMetrics['near_expiry_batches'])->toBe(1);
    expect($pharmacyMetrics['depleted_batches'])->toBe(1);

    // 3. Assert HealthReportService summary
    $reportService = new HealthReportService;
    $reportSummary = $reportService->getReportSummary('pharmacy_stock');

    expect($reportSummary['total_batches'])->toBe(4);
    expect($reportSummary['expired_batches'])->toBe(1);
    expect($reportSummary['near_expiry_batches'])->toBe(1);
    expect($reportSummary['depleted_batches'])->toBe(1);

    // 4. Assert ManagementDashboardQuery batch_health buckets
    $managementQuery = new ManagementDashboardQuery;
    $managementMetrics = $managementQuery->getMetrics(now()->subDays(7), now());

    expect($managementMetrics['batch_health']['active'])->toBe(1); // Batch C
    expect($managementMetrics['batch_health']['near_expiry'])->toBe(1); // Batch B
    expect($managementMetrics['batch_health']['expired'])->toBe(1); // Batch A
    expect($managementMetrics['batch_health']['depleted'])->toBe(1); // Batch D

    // Sum of mutually exclusive buckets equals total batches
    $sumBuckets = $managementMetrics['batch_health']['active']
        + $managementMetrics['batch_health']['near_expiry']
        + $managementMetrics['batch_health']['expired']
        + $managementMetrics['batch_health']['depleted'];
    expect($sumBuckets)->toBe(4);
});

test('pharmacy stock report view displays current snapshot semantics and excludes date pickers', function () {
    $pharmacist = createPhase5c2UserWithPermissions(['view-health-reports'], 'Apoteker POSKESTREN');

    $response = $this->actingAs($pharmacist)->get(route('reports.show', ['report_type' => 'pharmacy_stock']));

    $response->assertOk();
    $response->assertSee('Snapshot stok inventaris farmasi terkini', false);
    $response->assertSee('Cari Obat / No. Batch', false);

    // Assert absence of date range input fields for current snapshot report
    $response->assertDontSee('name="start_date"', false);
    $response->assertDontSee('name="end_date"', false);
});

test('pharmacy stock report supports keyword search filter on medicine name and batch number', function () {
    $pharmacist = createPhase5c2UserWithPermissions(['view-health-reports'], 'Apoteker POSKESTREN');

    $location = StockLocation::firstOrCreate(['code' => 'PHARM_TEST_SEARCH'], ['name' => 'Gudang Farmasi Search', 'is_active' => true]);
    $medicine = Medicine::firstOrCreate(['code' => 'MED-SEARCH-01'], [
        'generic_name' => 'Cetirizine',
        'brand_name' => 'Cetirizine Syrup 60ml UniqueXYZ',
        'dosage_form' => 'sirup',
        'base_unit' => 'botol',
        'is_active' => true,
    ]);

    MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-SEARCH-999',
        'expiry_date' => now()->addDays(90)->toDateString(),
        'initial_quantity' => 30,
        'current_quantity' => 25,
        'status' => 'active',
    ]);

    // 1. Search by brand keyword
    $response = $this->actingAs($pharmacist)->get(route('reports.show', [
        'report_type' => 'pharmacy_stock',
        'search' => 'UniqueXYZ',
    ]));

    $response->assertOk();
    $response->assertSee('BATCH-SEARCH-999');
    $response->assertSee('Cetirizine Syrup 60ml UniqueXYZ');

    // 2. Search by non-existent batch number
    $emptyResponse = $this->actingAs($pharmacist)->get(route('reports.show', [
        'report_type' => 'pharmacy_stock',
        'search' => 'NON_EXISTENT_BATCH_404',
    ]));

    $emptyResponse->assertOk();
    $emptyResponse->assertDontSee('BATCH-SEARCH-999');
    $emptyResponse->assertSee('Belum ada data pada periode ini');
});

test('pharmacy stock CSV export declares current snapshot metadata without fake date ranges', function () {
    $pharmacist = createPhase5c2UserWithPermissions(['view-health-reports', 'export-health-reports'], 'Apoteker POSKESTREN');

    $response = $this->actingAs($pharmacist)->get(route('reports.export', [
        'report_type' => 'pharmacy_stock',
        'search' => 'Paracetamol',
    ]));

    $response->assertOk();

    ob_start();
    $response->sendContent();
    $csvContent = (string) ob_get_clean();

    // Verify snapshot metadata headers
    expect($csvContent)->toContain('# LAPORAN POSKESTREN SABIRA HEALTH');
    expect($csvContent)->toContain('Snapshot Stok Farmasi Saat Ini');
    expect($csvContent)->toContain('# Diekspor Pada');
    expect($csvContent)->toContain('# Filter Pencarian');
    expect($csvContent)->toContain('Paracetamol');

    // Verify absence of fake date range header in snapshot export
    expect($csvContent)->not->toContain('# Filter Rentang');
});
