<?php

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;

function makeInventoryFilterUser(array $permissions = ['view-pharmacy-inventory']): User
{
    $user = User::factory()->create();
    $role = Role::create([
        'name' => 'inventory_filter_'.uniqid(),
        'display_name' => 'Inventory Filter Test',
    ]);

    foreach ($permissions as $permissionName) {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['display_name' => $permissionName],
        );
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role);

    return $user;
}

/** @return array{0: Medicine, 1: StockLocation} */
function makeInventoryFilterFixture(string $suffix = 'MAIN'): array
{
    $location = StockLocation::create([
        'code' => 'LOC-'.$suffix,
        'name' => 'Gudang '.$suffix,
        'is_active' => true,
    ]);
    $medicine = Medicine::create([
        'code' => 'MED-'.$suffix,
        'generic_name' => 'Cetirizine '.$suffix,
        'brand_name' => 'AlergiCare '.$suffix,
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
        'is_active' => true,
    ]);

    return [$medicine, $location];
}

function makeInventoryFilterBatch(
    Medicine $medicine,
    StockLocation $location,
    string $batchNumber,
    int $quantity = 20,
    mixed $expiryDate = null,
    string $status = 'active',
): MedicineBatch {
    return MedicineBatch::create([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => $batchNumber,
        'expiry_date' => $expiryDate ?? now()->addMonths(6),
        'initial_quantity' => max($quantity, 20),
        'current_quantity' => $quantity,
        'status' => $status,
    ]);
}

test('authorized staff can search inventory across medicine batch and location fields', function () {
    $user = makeInventoryFilterUser();
    [$medicine, $location] = makeInventoryFilterFixture('SEARCHABLE');
    makeInventoryFilterBatch($medicine, $location, 'LOT-UNIK-7788');

    foreach (['Cetirizine', 'AlergiCare', 'MED-SEARCHABLE', 'LOT-UNIK-7788', 'Gudang SEARCHABLE'] as $search) {
        $response = $this->actingAs($user)->get(route('pharmacy.inventory.index', ['search' => $search]));

        $response->assertOk();
        $response->assertSee('LOT-UNIK-7788');
        $response->assertSee('Ditemukan <span class="font-bold text-[var(--foreground)]">1</span> batch', false);
    }
});

test('inventory condition filters use current quantity and expiry semantics', function () {
    config(['pharmacy.expiry_warning_days' => 30]);

    $user = makeInventoryFilterUser();
    [$medicine, $location] = makeInventoryFilterFixture('CONDITION');

    makeInventoryFilterBatch($medicine, $location, 'LOT-AVAILABLE', 20, now()->addDays(60));
    makeInventoryFilterBatch($medicine, $location, 'LOT-NEAR', 20, now()->addDays(10));
    makeInventoryFilterBatch($medicine, $location, 'LOT-EXPIRED', 20, now()->subDay());
    makeInventoryFilterBatch($medicine, $location, 'LOT-DEPLETED', 0, now()->subDay(), 'depleted');

    $expectations = [
        'available' => 'LOT-AVAILABLE',
        'near_expiry' => 'LOT-NEAR',
        'expired' => 'LOT-EXPIRED',
        'depleted' => 'LOT-DEPLETED',
    ];

    foreach ($expectations as $condition => $visibleBatch) {
        $response = $this->actingAs($user)->get(route('pharmacy.inventory.index', ['condition' => $condition]));

        $response->assertOk()->assertSee($visibleBatch);

        foreach (array_diff($expectations, [$visibleBatch]) as $hiddenBatch) {
            $response->assertDontSee($hiddenBatch);
        }
    }
});

test('inventory can be filtered by stock location', function () {
    $user = makeInventoryFilterUser();
    [$medicineA, $locationA] = makeInventoryFilterFixture('LOCATION-A');
    [$medicineB, $locationB] = makeInventoryFilterFixture('LOCATION-B');
    makeInventoryFilterBatch($medicineA, $locationA, 'LOT-LOCATION-A');
    makeInventoryFilterBatch($medicineB, $locationB, 'LOT-LOCATION-B');

    $response = $this->actingAs($user)->get(route('pharmacy.inventory.index', ['location' => $locationB->id]));

    $response->assertOk();
    $response->assertSee('LOT-LOCATION-B');
    $response->assertDontSee('LOT-LOCATION-A');
});

test('inventory filter remains applied across pagination and has a distinct empty state', function () {
    $user = makeInventoryFilterUser();
    [$medicine, $location] = makeInventoryFilterFixture('PAGINATION');

    foreach (range(1, 16) as $number) {
        makeInventoryFilterBatch($medicine, $location, sprintf('LOT-PAGE-%02d', $number));
    }

    $paginated = $this->actingAs($user)->get(route('pharmacy.inventory.index', [
        'search' => 'LOT-PAGE',
        'condition' => 'available',
    ]));

    $paginated->assertOk();
    $paginated->assertSee('search=LOT-PAGE', false);
    $paginated->assertSee('condition=available', false);

    $empty = $this->actingAs($user)->get(route('pharmacy.inventory.index', ['search' => 'TIDAK-ADA-999']));
    $empty->assertOk();
    $empty->assertSee('Batch obat tidak ditemukan.');
    $empty->assertSee('Reset');
    $empty->assertDontSee('Belum ada batch obat yang diterima');
});

test('inventory filter validates input and preserves inventory authorization', function () {
    $authorized = makeInventoryFilterUser();
    $unauthorized = makeInventoryFilterUser([]);

    $this->actingAs($authorized)
        ->get(route('pharmacy.inventory.index', ['condition' => 'unknown']))
        ->assertSessionHasErrors('condition');

    $this->actingAs($authorized)
        ->get(route('pharmacy.inventory.index', ['search' => str_repeat('x', 101)]))
        ->assertSessionHasErrors('search');

    $this->actingAs($unauthorized)
        ->get(route('pharmacy.inventory.index'))
        ->assertForbidden();
});
