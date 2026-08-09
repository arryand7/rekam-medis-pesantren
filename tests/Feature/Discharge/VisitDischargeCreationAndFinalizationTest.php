<?php

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\VisitDischargeService;

function createReadyVisitForDischarge(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Sakit kepala dan lelah',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Sakit kepala sejak pagi',
        'examination_findings' => 'TD 110/70, Nadi 80',
        'assessment_summary' => 'Tension headache',
        'working_diagnosis' => 'Cephalea',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    return [$visit, $officer];
}

test('draft discharge can be prepared and transitions visit to discharge_prepared', function () {
    [$visit, $officer] = createReadyVisitForDischarge();
    $service = new VisitDischargeService;

    $discharge = $service->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Putra',
        'clinical_summary' => 'Pasien telah beristirahat dan membaik.',
        'final_condition' => 'Membaik, keluhan hilang',
        'activity_recommendation' => 'full_activity',
        'rest_recommendation' => 'Tidur cukup di asrama malam ini',
        'restriction_notes' => null,
    ], $officer);

    expect($discharge)->toBeInstanceOf(VisitDischarge::class);
    expect($discharge->status)->toBe('draft');
    expect($discharge->prepared_by_id)->toBe($officer->id);
    expect($visit->fresh()->status)->toBe('discharge_prepared');

    // Audit log
    $auditCount = AuditLog::where('action', 'visit_discharge.prepared')
        ->where('subject_id', $discharge->id)
        ->count();
    expect($auditCount)->toBe(1);
});

test('finalization atomically closes medical visit and creates version 1 snapshot', function () {
    [$visit, $officer] = createReadyVisitForDischarge();
    $service = new VisitDischargeService;

    $discharge = $service->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Putra',
        'clinical_summary' => 'Pasien telah beristirahat dan membaik.',
        'final_condition' => 'Membaik, keluhan hilang',
        'activity_recommendation' => 'full_activity',
    ], $officer);

    $finalized = $service->finalizeDischarge($discharge, [], $officer);

    expect($finalized->status)->toBe('finalized');
    expect($finalized->finalized_by_id)->toBe($officer->id);
    expect($finalized->finalized_at)->not->toBeNull();
    expect($visit->fresh()->status)->toBe('discharged');

    // Check version 1
    $version = $finalized->versions()->where('version_number', 1)->first();
    expect($version)->not->toBeNull();
    expect($version->checksum)->not->toBeEmpty();
    expect($version->summary_payload['visit_number'])->toBe($visit->visit_number);

    // Audit log
    $auditDischarge = AuditLog::where('action', 'visit_discharge.finalized')
        ->where('subject_id', $finalized->id)
        ->count();
    expect($auditDischarge)->toBe(1);

    $auditVisit = AuditLog::where('action', 'medical_visit.discharged')
        ->where('subject_id', $visit->id)
        ->count();
    expect($auditVisit)->toBe(1);
});

test('cannot finalize an already finalized discharge directly', function () {
    [$visit, $officer] = createReadyVisitForDischarge();
    $service = new VisitDischargeService;

    $discharge = $service->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama',
        'clinical_summary' => 'Pasien membaik.',
        'final_condition' => 'Sembuh',
        'activity_recommendation' => 'full_activity',
    ], $officer);

    $service->finalizeDischarge($discharge, [], $officer);

    expect(fn () => $service->finalizeDischarge($discharge, [], $officer))
        ->toThrow(Exception::class, 'Hanya draf kepulangan yang dapat difinalisasi.');
});

test('amendment creates new version while preserving original parent', function () {
    [$visit, $officer] = createReadyVisitForDischarge();
    $service = new VisitDischargeService;

    $discharge = $service->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama',
        'clinical_summary' => 'Pasien membaik.',
        'final_condition' => 'Sembuh',
        'activity_recommendation' => 'full_activity',
    ], $officer);

    $finalized = $service->finalizeDischarge($discharge, [], $officer);

    $amended = $service->amendDischarge($finalized, [
        'clinical_summary' => 'Pasien membaik, ditambah observasi hidrasi.',
        'rest_recommendation' => 'Istirahat 24 jam',
    ], 'Koreksi penambahan anjuran istirahat', $officer);

    expect($amended->status)->toBe('amended');
    expect($amended->amendment_reason)->toBe('Koreksi penambahan anjuran istirahat');
    expect($amended->lock_version)->toBe(2);

    // Check version 2 created
    $version2 = $amended->versions()->where('version_number', 2)->first();
    expect($version2)->not->toBeNull();
    expect($version2->redaction_notes)->toBe('Koreksi penambahan anjuran istirahat');
});

test('marking discharge as entered-in-error updates status and audits', function () {
    [$visit, $officer] = createReadyVisitForDischarge();
    $service = new VisitDischargeService;

    $discharge = $service->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama',
        'clinical_summary' => 'Draf keliru',
        'final_condition' => 'N/A',
        'activity_recommendation' => 'full_activity',
    ], $officer);

    $errDischarge = $service->markEnteredInError($discharge, 'Salah input pasien pada kunjungan', $officer);

    expect($errDischarge->status)->toBe('entered_in_error');
    expect($errDischarge->amendment_reason)->toBe('Salah input pasien pada kunjungan');

    $audit = AuditLog::where('action', 'visit_discharge.entered_in_error')
        ->where('subject_id', $errDischarge->id)
        ->count();
    expect($audit)->toBe(1);
});
