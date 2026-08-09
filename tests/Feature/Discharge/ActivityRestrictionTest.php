<?php

use App\Models\ActivityRestriction;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\VisitDischargeService;

function createDischargeForRestriction(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Terkilir pergelangan kaki',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Sprain ankle saat lompat tali',
        'examination_findings' => 'Bengkak lokal ankle dextra, nyeri tekan',
        'assessment_summary' => 'Sprain ankle grade 1',
        'working_diagnosis' => 'Ankle Sprain',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $dischargeService = new VisitDischargeService;
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'rest_required',
        'discharge_destination' => 'Asrama Santri',
        'clinical_summary' => 'Sprain grade 1, sudah dibebat elastic bandage.',
        'final_condition' => 'Nyeri terkontrol',
        'activity_recommendation' => 'limited_activity',
    ], $officer);

    return [$discharge, $officer];
}

test('activity restriction order can be issued with duration and rules', function () {
    [$discharge, $officer] = createDischargeForRestriction();
    $service = new VisitDischargeService;

    $restriction = $service->issueActivityRestriction($discharge, [
        'activity_status' => 'limited_activity',
        'effective_start' => now(),
        'effective_until' => now()->addDays(3),
        'restriction_type' => 'no_sports',
        'restriction_notes' => 'Dilarang mengikuti kegiatan olahraga dan baris-berbaris selama 3 hari.',
        'allowed_activity_notes' => 'Diperbolehkan mengikuti KBM di kelas dengan berjalan santai.',
    ], $officer);

    expect($restriction)->toBeInstanceOf(ActivityRestriction::class);
    expect($restriction->status)->toBe('active');
    expect($restriction->restriction_type)->toBe('no_sports');
    expect($restriction->isActive())->toBeTrue();
    expect($restriction->issued_by_id)->toBe($officer->id);

    $audit = AuditLog::where('action', 'activity_restriction.issued')
        ->where('subject_id', $restriction->id)
        ->count();
    expect($audit)->toBe(1);
});

test('activity restriction can be cancelled', function () {
    [$discharge, $officer] = createDischargeForRestriction();
    $service = new VisitDischargeService;

    $restriction = $service->issueActivityRestriction($discharge, [
        'activity_status' => 'rest',
        'effective_start' => now(),
        'restriction_type' => 'bed_rest',
        'restriction_notes' => 'Istirahat di kamar asrama.',
    ], $officer);

    $cancelled = $service->cancelActivityRestriction($restriction, $officer);

    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->isActive())->toBeFalse();

    $audit = AuditLog::where('action', 'activity_restriction.cancelled')
        ->where('subject_id', $restriction->id)
        ->count();
    expect($audit)->toBe(1);
});
