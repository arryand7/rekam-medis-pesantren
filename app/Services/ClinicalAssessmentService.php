<?php

namespace App\Services;

use App\Models\ClinicalAction;
use App\Models\ClinicalAssessment;
use App\Models\MedicalVisit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicalAssessmentService
{
    /**
     * Create or update a draft clinical assessment for a medical visit.
     */
    public function saveDraft(MedicalVisit $visit, array $data, ?User $actor = null): ClinicalAssessment
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            // Update visit status to under_assessment if it was waiting_assessment
            if ($visit->status === 'registered' || $visit->status === 'waiting_assessment') {
                $visit->update(['status' => 'under_assessment']);

                AuditLogService::log(
                    action: 'medical_visit.entered_assessment',
                    subjectType: 'MedicalVisit',
                    subjectId: $visit->id,
                    before: ['status' => 'waiting_assessment'],
                    after: ['status' => 'under_assessment'],
                    reason: 'Petugas mulai melakukan pengkajian medis'
                );
            }

            $assessment = ClinicalAssessment::where('medical_visit_id', $visit->id)
                ->where('status', 'draft')
                ->first();

            if ($assessment) {
                $assessment->update([
                    'history_current_illness' => $data['history_current_illness'] ?? $assessment->history_current_illness,
                    'relevant_history' => $data['relevant_history'] ?? $assessment->relevant_history,
                    'examination_findings' => $data['examination_findings'] ?? $assessment->examination_findings,
                    'assessment_summary' => $data['assessment_summary'] ?? $assessment->assessment_summary,
                    'working_diagnosis' => $data['working_diagnosis'] ?? $assessment->working_diagnosis,
                    'disposition_recommendation' => $data['disposition_recommendation'] ?? $assessment->disposition_recommendation,
                ]);
            } else {
                $assessment = ClinicalAssessment::create([
                    'medical_visit_id' => $visit->id,
                    'author_id' => $actor?->id,
                    'history_current_illness' => $data['history_current_illness'],
                    'relevant_history' => $data['relevant_history'] ?? null,
                    'examination_findings' => $data['examination_findings'],
                    'assessment_summary' => $data['assessment_summary'],
                    'working_diagnosis' => $data['working_diagnosis'] ?? null,
                    'disposition_recommendation' => $data['disposition_recommendation'] ?? null,
                    'status' => 'draft',
                ]);
            }

            return $assessment;
        });
    }

    /**
     * Finalize clinical assessment and transition visit to assessment_completed.
     */
    public function finalizeAssessment(ClinicalAssessment $assessment, ?User $actor = null): ClinicalAssessment
    {
        $actor = $actor ?? Auth::user();

        if ($assessment->status === 'finalized') {
            throw new Exception("Assessment {$assessment->id} sudah difinalisasi sebelumnya.");
        }

        return DB::transaction(function () use ($assessment, $actor) {
            /** @var MedicalVisit $visit */
            $visit = $assessment->medicalVisit;

            $assessment->update([
                'status' => 'finalized',
                'finalized_at' => now(),
                'finalized_by_id' => $actor?->id,
            ]);

            // Transition visit to assessment_completed
            $visit->update(['status' => 'assessment_completed']);

            AuditLogService::log(
                action: 'clinical_assessment.finalized',
                subjectType: 'ClinicalAssessment',
                subjectId: $assessment->id,
                before: ['status' => 'draft'],
                after: ['status' => 'finalized', 'disposition' => $assessment->disposition_recommendation],
                reason: 'Finalisasi pengkajian klinis dan penetapan rekomendasi disposisi'
            );

            AuditLogService::log(
                action: 'medical_visit.assessment_completed',
                subjectType: 'MedicalVisit',
                subjectId: $visit->id,
                before: ['status' => $visit->getOriginal('status')],
                after: ['status' => 'assessment_completed'],
                reason: 'Pengkajian medis kunjungan selesai'
            );

            return $assessment;
        });
    }

    /**
     * Add non-medication initial action.
     */
    public function recordAction(MedicalVisit $visit, array $data, ?User $actor = null): ClinicalAction
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            $action = ClinicalAction::create([
                'medical_visit_id' => $visit->id,
                'clinical_assessment_id' => $data['clinical_assessment_id'] ?? null,
                'action_type' => $data['action_type'] ?? 'first_aid',
                'description' => $data['description'],
                'performed_at' => now(),
                'performed_by_id' => $actor?->id,
                'status' => 'performed',
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLogService::log(
                action: 'clinical_action.recorded',
                subjectType: 'ClinicalAction',
                subjectId: $action->id,
                before: null,
                after: $action->toArray(),
                reason: "Tindakan awal non-obat ({$action->action_type}): {$action->description}"
            );

            return $action;
        });
    }
}
