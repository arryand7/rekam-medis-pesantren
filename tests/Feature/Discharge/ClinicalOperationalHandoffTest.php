<?php

use App\Models\AuditLog;
use App\Models\ClinicalOperationalHandoff;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\VisitDischargeService;

function createDischargeForHandoff(): array
{
    $person = Person::factory()->create(['name' => 'Ahmad Santri']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);

    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Demam dan lemas',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Demam sejak semalam, nafsu makan turun',
        'examination_findings' => 'Suhu 38.2C, faring hiperemis',
        'assessment_summary' => 'SENSITIVE_CLINICAL_NARRATIVE: Observasi infeksi saluran pernapasan atas akut',
        'working_diagnosis' => 'ISPA',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $dischargeService = new VisitDischargeService;
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'rest_required',
        'discharge_destination' => 'Asrama Umar bin Khattab',
        'clinical_summary' => 'Demam mereda setelah antipiretik.',
        'final_condition' => 'Membaik, suhu 37.0C',
        'activity_recommendation' => 'rest',
        'rest_recommendation' => 'Istirahat di kamar asrama selama 1x24 jam',
        'restriction_notes' => 'Tidak boleh piket kebersihan',
        'follow_up_required' => true,
        'follow_up_summary' => 'Cek suhu esok pagi',
    ], $officer);

    return [$discharge, $officer];
}

test('operational handoff snapshot strictly enforces minimum-necessary privacy', function () {
    [$discharge, $officer] = createDischargeForHandoff();
    $service = new VisitDischargeService;

    $handoff = $service->createOperationalHandoff($discharge, [
        'recipient_type' => 'dorm_supervisor',
        'recipient_reference' => 'Ustadz Pembina Asrama Umar',
        'purpose' => 'dorm_care_instruction',
        'special_instructions' => 'Mohon dipantau minum air hangat dan makan tepat waktu.',
    ], $officer);

    expect($handoff)->toBeInstanceOf(ClinicalOperationalHandoff::class);
    expect($handoff->status)->toBe('ready');
    expect($handoff->channel)->toBe('internal');

    $snapshot = $handoff->payload_snapshot;
    expect($snapshot['patient_name'])->toBe('Ahmad Santri');
    expect($snapshot['activity_recommendation'])->toBe('rest');
    expect($snapshot['rest_recommendation'])->toBe('Istirahat di kamar asrama selama 1x24 jam');
    expect($snapshot['restriction_notes'])->toBe('Tidak boleh piket kebersihan');
    expect($snapshot['special_instructions'])->toBe('Mohon dipantau minum air hangat dan makan tepat waktu.');

    // Crucial Privacy Check: Must NOT contain raw internal assessment narrative
    $snapshotJson = json_encode($snapshot);
    expect($snapshotJson)->not->toContain('SENSITIVE_CLINICAL_NARRATIVE');

    $audit = AuditLog::where('action', 'operational_handoff.prepared')
        ->where('subject_id', $handoff->id)
        ->count();
    expect($audit)->toBe(1);
});

test('operational handoff can be acknowledged with notes', function () {
    [$discharge, $officer] = createDischargeForHandoff();
    $supervisor = User::factory()->create();
    $service = new VisitDischargeService;

    $handoff = $service->createOperationalHandoff($discharge, [
        'recipient_type' => 'dorm_supervisor',
        'purpose' => 'dorm_care_instruction',
    ], $officer);

    $acknowledged = $service->acknowledgeOperationalHandoff($handoff, 'Santri telah berada di kamar asrama dan beristirahat.', $supervisor);

    expect($acknowledged->status)->toBe('acknowledged');
    expect($acknowledged->acknowledged_by_id)->toBe($supervisor->id);
    expect($acknowledged->acknowledged_at)->not->toBeNull();
    expect($acknowledged->acknowledgement_notes)->toBe('Santri telah berada di kamar asrama dan beristirahat.');

    $audit = AuditLog::where('action', 'operational_handoff.acknowledged')
        ->where('subject_id', $handoff->id)
        ->count();
    expect($audit)->toBe(1);
});
