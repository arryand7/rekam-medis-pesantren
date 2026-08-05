<?php

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Person;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\MedicationService;
use App\Services\PharmacyService;

test('authorized staff can create medication order without reducing stock', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan pusing',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-PARACETAMOL',
        'generic_name' => 'Paracetamol',
        'dosage_form' => 'tablet',
        'base_unit' => 'tablet',
    ], $officer);

    $location = StockLocation::create(['code' => 'APOTEK-ORD', 'name' => 'Apotek Order']);
    $pharmacyService->receiveStock([
        'medicine_id' => $medicine->id,
        'stock_location_id' => $location->id,
        'batch_number' => 'BATCH-ORD-01',
        'quantity' => 100,
    ], $officer);

    expect($medicine->fresh()->total_stock)->toBe(100);

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '500',
        'dose_unit' => 'mg',
        'frequency_text' => '3x1 sehari sesudah makan',
    ], $officer);

    expect($order->status)->toBe('active');
    expect($order->dose_value)->toBe('500');

    // VERIFIED: Order creation DOES NOT reduce pharmacy stock!
    expect($medicine->fresh()->total_stock)->toBe(100);
});

test('medication order displays active allergy warnings for patient', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    PatientAllergy::create([
        'patient_id' => $patient->id,
        'allergen' => 'Penicillin',
        'reaction' => 'Ruam kulit',
        'severity' => 'severe',
        'clinical_status' => 'active',
        'verification_status' => 'confirmed',
    ]);

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Infeksi kulit',
    ], $officer);

    $pharmacyService = new PharmacyService;
    $medicine = $pharmacyService->createMedicine([
        'code' => 'MED-AMOX-ORD',
        'generic_name' => 'Amoxicillin',
        'dosage_form' => 'capsule',
        'base_unit' => 'capsule',
    ], $officer);

    $medicationService = new MedicationService;
    $order = $medicationService->createOrder($visit, [
        'medicine_id' => $medicine->id,
        'dose_value' => '500',
        'dose_unit' => 'mg',
        'frequency_text' => '3x1 sehari',
        'allergy_acknowledgement_reason' => 'Pasien memerlukan antibiotik penanganan khusus dengan pengawasan tim medis',
    ], $officer);

    expect($order->safetyAcknowledgements->count())->toBe(1);
    expect($order->safetyAcknowledgements->first()->warning_type)->toBe('active_allergy_warning');
});
