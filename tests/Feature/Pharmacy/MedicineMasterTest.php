<?php

use App\Models\User;
use App\Services\PharmacyService;

test('authorized staff can create a new medicine master record', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-PARACETAMOL-500',
        'generic_name' => 'Paracetamol',
        'brand_name' => 'Sanamol',
        'dosage_form' => 'tablet',
        'strength_text' => '500 mg',
        'base_unit' => 'tablet',
        'minimum_stock' => 50,
    ], $staff);

    expect($medicine->code)->toBe('MED-PARACETAMOL-500');
    expect($medicine->generic_name)->toBe('Paracetamol');
    expect($medicine->minimum_stock)->toBe(50);
    expect($medicine->is_active)->toBeTrue();
});

test('duplicate medicine code registration is rejected', function () {
    $staff = User::factory()->create();
    $pharmacyService = new PharmacyService;

    $pharmacyService->createMedicine([
        'code' => 'MED-CTM-4',
        'generic_name' => 'Chlorpheniramine Maleate',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $staff);

    expect(fn () => $pharmacyService->createMedicine([
        'code' => 'MED-CTM-4',
        'generic_name' => 'CTM Duplikat',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $staff))->toThrow(Exception::class);
});
