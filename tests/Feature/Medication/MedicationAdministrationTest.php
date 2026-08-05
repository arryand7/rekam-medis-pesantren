<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\MedicationService;
use App\Services\PharmacyService;

test('administering medication deducts batch stock atomically and creates stock movement entry', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Sakit kepala',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-PARACETAMOL-ADMIN',
        'generic_name' => 'Paracetamol',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $officer);

    $location = StockLocation::create(['code' => 'APOTEK-ADM', 'name' => 'Apotek Admin']);
    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-PCT-55',
        'quantity' => 20,
    ], $officer);

    $batch = $receipt->batch;

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '500',
        'dose_unit' => 'mg',
        'frequency_text' => '3x1 sehari',
        'quantity_per_administration' => 1,
    ], $officer);

    $admin = $medicationService->scheduleAdministration($order, [], $officer);
    expect($batch->fresh()->current_quantity)->toBe(20);

    // Administer medication
    $medicationService->administerMedication($admin, $batch, $officer);

    expect($admin->fresh()->status)->toBe('administered');
    expect($admin->fresh()->stock_movement_id)->not->toBeNull();

    // ATOMIC STOCK ISSUE VERIFIED!
    expect($batch->fresh()->current_quantity)->toBe(19);
    expect($medicine->fresh()->total_stock)->toBe(19);
});

test('administering medication with insufficient batch stock throws exception', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Pusing',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-EMPTY-STOCK',
        'generic_name' => 'Obat Stok Habis',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $officer);

    $location = StockLocation::create(['code' => 'APOTEK-EMP', 'name' => 'Apotek Empty']);
    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-EMP-01',
        'quantity' => 1,
    ], $officer);

    $batch = $receipt->batch;

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '1',
        'dose_unit' => 'tablet',
        'frequency_text' => '1x1',
        'quantity_per_administration' => 5, // Demands 5, available is 1
    ], $officer);

    $admin = $medicationService->scheduleAdministration($order, [], $officer);

    // Insufficient stock -> Must throw Exception
    expect(fn () => $medicationService->administerMedication($admin, $batch, $officer))->toThrow(Exception::class);
});
