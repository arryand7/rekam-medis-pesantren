<?php

use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\MedicalVisitService;
use App\Services\ObservationService;

test('shift handover acknowledgment atomically transfers responsible officer', function () {
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer1 = User::factory()->create(['name' => 'Petugas Shift Pagi']);
    $officer2 = User::factory()->create(['name' => 'Petugas Shift Malam']);

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Observasi radang',
    ], $officer1);

    $obsService = new ObservationService;
    $episode = $obsService->startEpisode($visit, [
        'reason' => 'Observasi shift pagi',
    ], $officer1);

    expect($episode->responsible_officer_id)->toBe($officer1->id);

    // Shift 1 submits handover to Shift 2
    $handover = $obsService->submitHandover($episode, [
        'to_user_id' => $officer2->id,
        'summary' => 'Santri dalam keadaan stabil, telah istirahat siang',
        'current_condition' => 'Suhu 37.0C, tidak ada sesak',
        'pending_tasks' => 'Cek suhu kembali jam 20:00 WIB',
    ], $officer1);

    expect($handover->status)->toBe('submitted');

    // Shift 2 acknowledges handover
    $obsService->acknowledgeHandover($handover, $officer2);

    expect($handover->fresh()->status)->toBe('acknowledged');
    expect($handover->fresh()->acknowledged_by_id)->toBe($officer2->id);

    // ATOMIC RESPONSIBILITY TRANSFER VERIFIED!
    expect($episode->fresh()->responsible_officer_id)->toBe($officer2->id);
});
