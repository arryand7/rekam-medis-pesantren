<?php

use App\Models\StockLocation;
use App\Models\User;
use App\Services\PharmacyService;

test('reversing stock movement atomically updates batch quantity and links reversal movement', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-ANTASIDA',
        'generic_name' => 'Antasida Doen',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $staff);

    $location = StockLocation::create(['code' => 'APOTEK-REV', 'name' => 'Apotek Reversal']);

    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-ANT-01',
        'quantity' => 50,
    ], $staff);

    $batch = $receipt->batch;
    expect($batch->current_quantity)->toBe(50);

    // Reversing the receipt movement should deduct 50 from batch
    $reversal = $pharmacyService->reverseMovement($receipt, 'Salah catat penerimaan jumlah', $staff);

    expect($reversal->movement_type)->toBe('reversal');
    expect($reversal->reverses_movement_id)->toBe($receipt->id);
    expect($batch->fresh()->current_quantity)->toBe(0);

    // Second reversal attempt of same movement must throw Exception
    expect(fn () => $pharmacyService->reverseMovement($receipt, 'Reversal kedua', $staff))->toThrow(Exception::class);
});
