<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\MedicationService;
use App\Services\PharmacyService;

test('correcting administration with entered_in_error status reverses stock issue and restores batch balance', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Tes koreksi obat',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-REVERSAL-TEST',
        'generic_name' => 'Obat Reversal',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $officer);

    $location = StockLocation::create(['code' => 'APOTEK-REV-MED', 'name' => 'Apotek Reversal Med']);
    $receipt = $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-REV-100',
        'quantity' => 10,
    ], $officer);

    $batch = $receipt->batch;

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '1',
        'dose_unit' => 'tablet',
        'frequency_text' => '1x1',
    ], $officer);

    $admin = $medicationService->scheduleAdministration($order, [], $officer);
    $medicationService->administerMedication($admin, $batch, $officer);

    expect($batch->fresh()->current_quantity)->toBe(9);

    // Correct administration as entered_in_error
    $medicationService->correctAdministration($admin, 'Salah catat pasien', $officer);

    expect($admin->fresh()->status)->toBe('entered_in_error');

    // ATOMIC STOCK REVERSAL VERIFIED!
    expect($batch->fresh()->current_quantity)->toBe(10);
    expect($medicine->fresh()->total_stock)->toBe(10);
});
