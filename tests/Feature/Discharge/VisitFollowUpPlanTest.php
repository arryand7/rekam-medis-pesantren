<?php

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitFollowUpPlan;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\VisitDischargeService;

function createDischargeWithFollowUp(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Luka lecet di lutut pasca olahraga',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Jatuh saat bermain futsal',
        'examination_findings' => 'Vulnus excoriatum genu dextra',
        'assessment_summary' => 'Luka lecet bersih, sudah didisinfeksi',
        'working_diagnosis' => 'Vulnus Excoriatum',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $dischargeService = new VisitDischargeService;
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Santri',
        'clinical_summary' => 'Luka telah dirawat dan dibalut steril.',
        'final_condition' => 'Stabil, luka bersih',
        'activity_recommendation' => 'limited_activity',
        'follow_up_required' => true,
        'follow_up_summary' => 'Ganti perban dan evaluasi luka 2 hari lagi',
    ], $officer);

    return [$discharge, $officer];
}

test('follow-up plan can be added with structured instructions and due date', function () {
    [$discharge, $officer] = createDischargeWithFollowUp();
    $service = new VisitDischargeService;

    $plan = $service->addFollowUpPlan($discharge, [
        'follow_up_type' => 'wound_review',
        'due_at' => now()->addDays(2),
        'instructions' => 'Kontrol ke Poskestren untuk ganti verban dan evaluasi tanda infeksi.',
        'responsible_party_type' => 'dorm_supervisor',
        'responsible_party_reference' => 'Ustadz Pembina Asrama 2',
    ], $officer);

    expect($plan)->toBeInstanceOf(VisitFollowUpPlan::class);
    expect($plan->status)->toBe('planned');
    expect($plan->follow_up_type)->toBe('wound_review');
    expect($plan->created_by_id)->toBe($officer->id);

    $audit = AuditLog::where('action', 'visit_follow_up.planned')
        ->where('subject_id', $plan->id)
        ->count();
    expect($audit)->toBe(1);
});

test('follow-up plan can be manually completed with notes', function () {
    [$discharge, $officer] = createDischargeWithFollowUp();
    $service = new VisitDischargeService;

    $plan = $service->addFollowUpPlan($discharge, [
        'follow_up_type' => 'wound_review',
        'due_at' => now()->addDays(2),
        'instructions' => 'Ganti balutan luka.',
    ], $officer);

    $completed = $service->completeFollowUpPlan($plan, 'Luka mengering baik, tidak ada pus, verban diganti baru.', $officer);

    expect($completed->status)->toBe('completed');
    expect($completed->completed_by_id)->toBe($officer->id);
    expect($completed->completed_at)->not->toBeNull();
    expect($completed->notes)->toBe('Luka mengering baik, tidak ada pus, verban diganti baru.');

    $audit = AuditLog::where('action', 'visit_follow_up.completed')
        ->where('subject_id', $plan->id)
        ->count();
    expect($audit)->toBe(1);
});

test('follow-up plan can be cancelled with cancellation reason', function () {
    [$discharge, $officer] = createDischargeWithFollowUp();
    $service = new VisitDischargeService;

    $plan = $service->addFollowUpPlan($discharge, [
        'follow_up_type' => 'wound_review',
        'due_at' => now()->addDays(2),
        'instructions' => 'Ganti balutan luka.',
    ], $officer);

    $cancelled = $service->cancelFollowUpPlan($plan, 'Santri telah kontrol mandiri ke klinik keluarga saat libur.', $officer);

    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->cancellation_reason)->toBe('Santri telah kontrol mandiri ke klinik keluarga saat libur.');

    $audit = AuditLog::where('action', 'visit_follow_up.cancelled')
        ->where('subject_id', $plan->id)
        ->count();
    expect($audit)->toBe(1);
});
