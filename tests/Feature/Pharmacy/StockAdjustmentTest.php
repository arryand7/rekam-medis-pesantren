<?php

use App\Models\StockLocation;
use App\Models\User;
use App\Services\PharmacyService;

test('stock adjustment out deducts batch quantity and creates movement entry', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-OBH-SYRUP',
        'generic_name' => 'OBH Batuk',
        'dosage_form' => 'syrup',
        'base_unit' => 'botol',
    ], $staff);

    $location = StockLocation::create(['code' => 'GUDANG-TEST', 'name' => 'Gudang Test']);

    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-OBH-10',
        'quantity' => 20,
    ], $staff);

    $batch = $receipt->batch;

    $adjustment = $pharmacyService->adjustStock([
        'medicine_batch_id' => $batch->id,
        'movement_type' => 'adjustment_out',
        'quantity' => 5,
        'reason' => 'Botol botol kemasan pecah saat pengataan gudang',
    ], $staff);

    expect($adjustment->movement_type)->toBe('adjustment_out');
    expect($batch->fresh()->current_quantity)->toBe(15);
});

test('no negative stock guard prevents adjustment out exceeding current batch quantity', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-VIT-C',
        'generic_name' => 'Vitamin C 500mg',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $staff);

    $location = StockLocation::create(['code' => 'APOTEK-C', 'name' => 'Apotek C']);

    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-VITC-01',
        'quantity' => 10,
    ], $staff);

    $batch = $receipt->batch;

    // Request adjustment out of 15 (available is 10) -> Must throw Exception!
    expect(fn () => $pharmacyService->adjustStock([
        'medicine_batch_id' => $batch->id,
        'movement_type' => 'adjustment_out',
        'quantity' => 15,
        'reason' => 'Tes pengurangan berlebih',
    ], $staff))->toThrow(Exception::class);
});
