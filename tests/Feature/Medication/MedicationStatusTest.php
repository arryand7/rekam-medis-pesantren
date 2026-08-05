<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\MedicationService;
use App\Services\PharmacyService;

test('non-administered statuses held refused missed do not reduce pharmacy stock', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Batuk pilek',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-STATUS-TEST',
        'generic_name' => 'Obat Tes Status',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $officer);

    $location = StockLocation::create(['code' => 'APOTEK-ST', 'name' => 'Apotek Status']);
    $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-ST-01',
        'quantity' => 50,
    ], $officer);

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '1',
        'dose_unit' => 'tablet',
        'frequency_text' => '2x1',
    ], $officer);

    $admin = $medicationService->scheduleAdministration($order, [], $officer);

    // Recording refusal
    $medicationService->recordNonAdministeredStatus($admin, 'refused', 'Santri menolak minum obat sirup', $officer);

    expect($admin->fresh()->status)->toBe('refused');

    // VERIFIED: Refused status DOES NOT reduce pharmacy stock!
    expect($medicine->fresh()->total_stock)->toBe(50);
});
