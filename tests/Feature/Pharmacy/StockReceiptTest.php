<?php

use App\Models\StockLocation;
use App\Models\User;
use App\Services\PharmacyService;

test('receiving medicine stock creates batch and append-only receipt movement', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-AMOX-500',
        'generic_name' => 'Amoxicillin',
        'dosage_form' => 'capsule',
        'base_unit' => 'capsule',
    ], $staff);

    $location = StockLocation::create(['code' => 'APOTEK-TEST', 'name' => 'Apotek Test']);

    $movement = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-AMX-001',
        'expiry_date' => now()->addYear()->format('Y-m-d'),
        'quantity' => 100,
        'supplier_name' => 'Dinkes Kabupaten',
    ], $staff);

    expect($movement->movement_type)->toBe('receipt');
    expect($movement->quantity)->toBe(100);

    $batch = $movement->batch;
    expect($batch->batch_number)->toBe('BATCH-AMX-001');
    expect($batch->current_quantity)->toBe(100);
    expect($medicine->fresh()->total_stock)->toBe(100);
});
