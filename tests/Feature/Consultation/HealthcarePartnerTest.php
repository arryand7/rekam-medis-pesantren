<?php

use App\Models\User;
use App\Services\ClinicalConsultationService;

test('authorized user can register healthcare partner facility and clinician contact', function () {
    $staff = User::factory()->create();
    $consultationService = new ClinicalConsultationService;

    $partner = $consultationService->createPartner([
        'code' => 'PKM-AMPEL-TEST',
        'name' => 'Puskesmas Ampel Test',
        'partner_type' => 'puskesmas',
        'phone' => '031-5550199',
    ], $staff);

    expect($partner->code)->toBe('PKM-AMPEL-TEST');
    expect($partner->is_active)->toBeTrue();

    $contact = $consultationService->createPartnerContact($partner, [
        'name' => 'dr. Budi Santoso',
        'profession' => 'Dokter Umum',
        'official_contact' => '08123456789',
    ], $staff);

    expect($contact->name)->toBe('dr. Budi Santoso');
    expect($contact->isVerified())->toBeTrue();
});
