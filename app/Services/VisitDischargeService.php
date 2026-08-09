<?php

namespace App\Services;

use App\Actions\Discharge\EvaluateVisitDischargeReadinessAction;
use App\Models\ActivityRestriction;
use App\Models\ClinicalAssessment;
use App\Models\ClinicalOperationalHandoff;
use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Models\VisitDischargeVersion;
use App\Models\VisitFollowUpPlan;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service managing clinical discharge, follow-up, activity restrictions,
 * and internal operational handoffs for medical visits.
 */
class VisitDischargeService
{
    public function __construct(
        protected EvaluateVisitDischargeReadinessAction $readinessEvaluator = new EvaluateVisitDischargeReadinessAction
    ) {}

    /**
     * Prepare a draft discharge for a medical visit.
     */
    public function prepareDraft(MedicalVisit $visit, array $data, ?User $actor = null): VisitDischarge
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            /** @var MedicalVisit $lockedVisit */
            $lockedVisit = MedicalVisit::where('id', $visit->id)->lockForUpdate()->firstOrFail();

            $readiness = $this->readinessEvaluator->execute($lockedVisit);
            if (! $readiness['is_ready']) {
                $blockerList = implode(' ', $readiness['technical_blockers']);
                throw new Exception("Kunjungan belum siap untuk proses kepulangan: {$blockerList}");
            }

            // Check if existing discharge draft exists
            $discharge = VisitDischarge::where('medical_visit_id', $lockedVisit->id)->first();

            $payload = [
                'medical_visit_id' => $lockedVisit->id,
                'discharge_type' => $data['discharge_type'],
                'discharge_destination' => $data['discharge_destination'],
                'clinical_summary' => trim($data['clinical_summary']),
                'final_condition' => $data['final_condition'],
                'activity_recommendation' => $data['activity_recommendation'],
                'rest_recommendation' => ! empty($data['rest_recommendation']) ? trim($data['rest_recommendation']) : null,
                'restriction_notes' => ! empty($data['restriction_notes']) ? trim($data['restriction_notes']) : null,
                'follow_up_required' => ! empty($data['follow_up_required']),
                'follow_up_summary' => ! empty($data['follow_up_summary']) ? trim($data['follow_up_summary']) : null,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'follow_up_partner_id' => $data['follow_up_partner_id'] ?? null,
                'prepared_by_id' => $actor?->id,
                'prepared_at' => now(),
                'status' => 'draft',
            ];

            if ($discharge) {
                if ($discharge->isFinalized()) {
                    throw new Exception('Kepulangan kunjungan ini sudah difinalisasi. Gunakan workflow amandemen untuk mengubah.');
                }
                $discharge->update($payload);
            } else {
                $discharge = VisitDischarge::create($payload);
            }

            // Transition visit status to discharge_prepared
            $lockedVisit->update(['status' => 'discharge_prepared']);

            AuditLogService::log(
                action: 'visit_discharge.prepared',
                subjectType: 'VisitDischarge',
                subjectId: $discharge->id,
                before: null,
                after: $discharge->toArray(),
                reason: 'Draf kepulangan kunjungan '.$lockedVisit->visit_number.' disiapkan oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            return $discharge;
        });
    }

    /**
     * Finalize a visit discharge, closing the medical visit atomically.
     */
    public function finalizeDischarge(VisitDischarge $discharge, array $data = [], ?User $actor = null): VisitDischarge
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $data, $actor) {
            /** @var VisitDischarge $lockedDischarge */
            $lockedDischarge = VisitDischarge::where('id', $discharge->id)->lockForUpdate()->firstOrFail();

            if ($lockedDischarge->status !== 'draft') {
                throw new Exception('Hanya draf kepulangan yang dapat difinalisasi.');
            }

            /** @var MedicalVisit $lockedVisit */
            $lockedVisit = MedicalVisit::where('id', $lockedDischarge->medical_visit_id)->lockForUpdate()->firstOrFail();

            $readiness = $this->readinessEvaluator->execute($lockedVisit);
            if (! $readiness['is_ready']) {
                $blockerList = implode(' ', $readiness['technical_blockers']);
                throw new Exception("Kunjungan belum siap untuk finalisasi kepulangan: {$blockerList}");
            }

            // Update discharge fields if additional data provided
            if (! empty($data)) {
                $lockedDischarge->fill(array_filter([
                    'clinical_summary' => isset($data['clinical_summary']) ? trim($data['clinical_summary']) : null,
                    'final_condition' => $data['final_condition'] ?? null,
                    'activity_recommendation' => $data['activity_recommendation'] ?? null,
                    'rest_recommendation' => isset($data['rest_recommendation']) ? trim($data['rest_recommendation']) : null,
                    'restriction_notes' => isset($data['restriction_notes']) ? trim($data['restriction_notes']) : null,
                ]));
            }

            $lockedDischarge->status = 'finalized';
            $lockedDischarge->finalized_by_id = $actor?->id;
            $lockedDischarge->finalized_at = now();
            $lockedDischarge->save();

            // Atomically close medical visit
            $lockedVisit->update(['status' => 'discharged']);

            // Create Version 1 snapshot
            $summaryPayload = $this->buildSummaryPayload($lockedDischarge, $lockedVisit);
            $checksum = hash('sha256', (string) json_encode($summaryPayload));

            VisitDischargeVersion::create([
                'visit_discharge_id' => $lockedDischarge->id,
                'version_number' => 1,
                'summary_payload' => $summaryPayload,
                'checksum' => $checksum,
                'authored_by_id' => $actor?->id,
                'finalized_at' => now(),
            ]);

            AuditLogService::log(
                action: 'visit_discharge.finalized',
                subjectType: 'VisitDischarge',
                subjectId: $lockedDischarge->id,
                before: null,
                after: $lockedDischarge->toArray(),
                reason: 'Kepulangan kunjungan '.$lockedVisit->visit_number.' difinalisasi oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            AuditLogService::log(
                action: 'medical_visit.discharged',
                subjectType: 'MedicalVisit',
                subjectId: $lockedVisit->id,
                before: null,
                after: ['visit_number' => $lockedVisit->visit_number, 'discharged_at' => now()->toIso8601String()],
                reason: "Kunjungan medis {$lockedVisit->visit_number} ditutup (discharged)"
            );

            return $lockedDischarge;
        });
    }

    /**
     * Amend a finalized discharge record, creating a new versioned snapshot.
     */
    public function amendDischarge(VisitDischarge $discharge, array $data, string $amendmentReason, ?User $actor = null): VisitDischarge
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $data, $amendmentReason, $actor) {
            /** @var VisitDischarge $original */
            $original = VisitDischarge::where('id', $discharge->id)->lockForUpdate()->firstOrFail();

            if (! $original->isFinalized()) {
                throw new Exception('Hanya kepulangan yang sudah difinalisasi yang dapat diamandemen.');
            }

            $beforeState = $original->toArray();

            // Update discharge aggregate with amended fields
            $original->update([
                'discharge_type' => $data['discharge_type'] ?? $original->discharge_type,
                'discharge_destination' => $data['discharge_destination'] ?? $original->discharge_destination,
                'clinical_summary' => trim($data['clinical_summary'] ?? $original->clinical_summary),
                'final_condition' => $data['final_condition'] ?? $original->final_condition,
                'activity_recommendation' => $data['activity_recommendation'] ?? $original->activity_recommendation,
                'rest_recommendation' => isset($data['rest_recommendation']) ? trim($data['rest_recommendation']) : $original->rest_recommendation,
                'restriction_notes' => isset($data['restriction_notes']) ? trim($data['restriction_notes']) : $original->restriction_notes,
                'follow_up_required' => isset($data['follow_up_required']) ? (bool) $data['follow_up_required'] : $original->follow_up_required,
                'follow_up_summary' => isset($data['follow_up_summary']) ? trim($data['follow_up_summary']) : $original->follow_up_summary,
                'follow_up_date' => $data['follow_up_date'] ?? $original->follow_up_date,
                'follow_up_partner_id' => $data['follow_up_partner_id'] ?? $original->follow_up_partner_id,
                'status' => 'amended',
                'amendment_reason' => trim($amendmentReason),
                'lock_version' => $original->lock_version + 1,
            ]);

            // Create new Version record
            $nextVersionNumber = (int) VisitDischargeVersion::where('visit_discharge_id', $original->id)->max('version_number') + 1;
            /** @var MedicalVisit $visit */
            $visit = $original->medicalVisit;
            $summaryPayload = $this->buildSummaryPayload($original, $visit);
            $checksum = hash('sha256', (string) json_encode($summaryPayload));

            VisitDischargeVersion::create([
                'visit_discharge_id' => $original->id,
                'version_number' => $nextVersionNumber,
                'summary_payload' => $summaryPayload,
                'checksum' => $checksum,
                'authored_by_id' => $actor?->id,
                'finalized_at' => now(),
                'redaction_notes' => $amendmentReason,
            ]);

            AuditLogService::log(
                action: 'visit_discharge.amended',
                subjectType: 'VisitDischarge',
                subjectId: $original->id,
                before: $beforeState,
                after: $original->toArray(),
                reason: "Kepulangan kunjungan {$visit->visit_number} diamandemen: {$amendmentReason}"
            );

            return $original;
        });
    }

    /**
     * Mark discharge record as entered-in-error.
     */
    public function markEnteredInError(VisitDischarge $discharge, string $reason, ?User $actor = null): VisitDischarge
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $reason) {
            /** @var VisitDischarge $locked */
            $locked = VisitDischarge::where('id', $discharge->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'entered_in_error',
                'amendment_reason' => trim($reason),
            ]);

            AuditLogService::log(
                action: 'visit_discharge.entered_in_error',
                subjectType: 'VisitDischarge',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: "Catatan kepulangan ditandai keliru (entered_in_error): {$reason}"
            );

            return $locked;
        });
    }

    /**
     * Add a follow-up plan to a discharge.
     */
    public function addFollowUpPlan(VisitDischarge $discharge, array $data, ?User $actor = null): VisitFollowUpPlan
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $data, $actor) {
            $plan = VisitFollowUpPlan::create([
                'visit_discharge_id' => $discharge->id,
                'follow_up_type' => $data['follow_up_type'],
                'due_at' => $data['due_at'] ?? null,
                'healthcare_partner_id' => $data['healthcare_partner_id'] ?? null,
                'instructions' => trim($data['instructions']),
                'responsible_party_type' => $data['responsible_party_type'] ?? null,
                'responsible_party_reference' => $data['responsible_party_reference'] ?? null,
                'status' => 'planned',
                'created_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'visit_follow_up.planned',
                subjectType: 'VisitFollowUpPlan',
                subjectId: $plan->id,
                before: null,
                after: $plan->toArray(),
                reason: "Rencana tindak lanjut {$plan->follow_up_type} ditambahkan"
            );

            return $plan;
        });
    }

    /**
     * Complete a follow-up plan manually.
     */
    public function completeFollowUpPlan(VisitFollowUpPlan $plan, ?string $notes = null, ?User $actor = null): VisitFollowUpPlan
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($plan, $notes, $actor) {
            /** @var VisitFollowUpPlan $locked */
            $locked = VisitFollowUpPlan::where('id', $plan->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by_id' => $actor?->id,
                'notes' => $notes !== null ? trim($notes) : $locked->notes,
            ]);

            AuditLogService::log(
                action: 'visit_follow_up.completed',
                subjectType: 'VisitFollowUpPlan',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: 'Rencana tindak lanjut diselesaikan secara manual'
            );

            return $locked;
        });
    }

    /**
     * Cancel a follow-up plan.
     */
    public function cancelFollowUpPlan(VisitFollowUpPlan $plan, string $reason, ?User $actor = null): VisitFollowUpPlan
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($plan, $reason) {
            /** @var VisitFollowUpPlan $locked */
            $locked = VisitFollowUpPlan::where('id', $plan->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'cancelled',
                'cancellation_reason' => trim($reason),
            ]);

            AuditLogService::log(
                action: 'visit_follow_up.cancelled',
                subjectType: 'VisitFollowUpPlan',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: "Rencana tindak lanjut dibatalkan: {$reason}"
            );

            return $locked;
        });
    }

    /**
     * Issue an activity restriction order.
     */
    public function issueActivityRestriction(VisitDischarge $discharge, array $data, ?User $actor = null): ActivityRestriction
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $data, $actor) {
            $restriction = ActivityRestriction::create([
                'visit_discharge_id' => $discharge->id,
                'activity_status' => $data['activity_status'],
                'effective_start' => $data['effective_start'] ?? now(),
                'effective_until' => $data['effective_until'] ?? null,
                'restriction_type' => $data['restriction_type'],
                'restriction_notes' => trim($data['restriction_notes']),
                'allowed_activity_notes' => ! empty($data['allowed_activity_notes']) ? trim($data['allowed_activity_notes']) : null,
                'issued_by_id' => $actor?->id,
                'issued_at' => now(),
                'review_date' => $data['review_date'] ?? null,
                'status' => 'active',
            ]);

            AuditLogService::log(
                action: 'activity_restriction.issued',
                subjectType: 'ActivityRestriction',
                subjectId: $restriction->id,
                before: null,
                after: $restriction->toArray(),
                reason: "Rekomendasi pembatasan aktivitas {$restriction->restriction_type} diterbitkan"
            );

            return $restriction;
        });
    }

    /**
     * Cancel an active activity restriction.
     */
    public function cancelActivityRestriction(ActivityRestriction $restriction, ?User $actor = null): ActivityRestriction
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($restriction) {
            /** @var ActivityRestriction $locked */
            $locked = ActivityRestriction::where('id', $restriction->id)->lockForUpdate()->firstOrFail();

            $locked->update(['status' => 'cancelled']);

            AuditLogService::log(
                action: 'activity_restriction.cancelled',
                subjectType: 'ActivityRestriction',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: 'Pembatasan aktivitas dibatalkan'
            );

            return $locked;
        });
    }

    /**
     * Create an internal operational handoff adhering to minimum-necessary privacy.
     */
    public function createOperationalHandoff(VisitDischarge $discharge, array $data, ?User $actor = null): ClinicalOperationalHandoff
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $data, $actor) {
            /** @var MedicalVisit $visit */
            $visit = $discharge->medicalVisit;

            // Generate minimum necessary payload
            /** @var Patient|null $patient */
            $patient = $visit->patient;
            /** @var Person|null $person */
            $person = $patient?->person;

            $payloadSnapshot = [
                'patient_number' => $patient !== null ? $patient->patient_number : '-',
                'patient_name' => $person !== null ? $person->full_name : '-',
                'activity_recommendation' => $discharge->activity_recommendation,
                'rest_recommendation' => $discharge->rest_recommendation,
                'restriction_notes' => $discharge->restriction_notes,
                'follow_up_required' => $discharge->follow_up_required,
                'follow_up_summary' => $discharge->follow_up_summary,
                'special_instructions' => $data['special_instructions'] ?? null,
            ];

            $handoff = ClinicalOperationalHandoff::create([
                'medical_visit_id' => $visit->id,
                'visit_discharge_id' => $discharge->id,
                'recipient_type' => $data['recipient_type'],
                'recipient_reference' => $data['recipient_reference'] ?? null,
                'purpose' => $data['purpose'],
                'payload_snapshot' => $payloadSnapshot,
                'status' => 'ready',
                'prepared_by_id' => $actor?->id,
                'prepared_at' => now(),
                'channel' => 'internal',
            ]);

            AuditLogService::log(
                action: 'operational_handoff.prepared',
                subjectType: 'ClinicalOperationalHandoff',
                subjectId: $handoff->id,
                before: null,
                after: $handoff->toArray(),
                reason: "Handoff operasional internal untuk {$handoff->recipient_type} disiapkan"
            );

            return $handoff;
        });
    }

    /**
     * Acknowledge receipt of an operational handoff.
     */
    public function acknowledgeOperationalHandoff(ClinicalOperationalHandoff $handoff, ?string $notes = null, ?User $actor = null): ClinicalOperationalHandoff
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($handoff, $notes, $actor) {
            /** @var ClinicalOperationalHandoff $locked */
            $locked = ClinicalOperationalHandoff::where('id', $handoff->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'acknowledged_by_id' => $actor?->id,
                'acknowledgement_notes' => $notes !== null ? trim($notes) : null,
            ]);

            AuditLogService::log(
                action: 'operational_handoff.acknowledged',
                subjectType: 'ClinicalOperationalHandoff',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: 'Handoff operasional dikonfirmasi penerimaannya oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            return $locked;
        });
    }

    /**
     * Build minimum necessary versioned summary payload for discharge.
     *
     * @return array<string, mixed>
     */
    protected function buildSummaryPayload(VisitDischarge $discharge, MedicalVisit $visit): array
    {
        /** @var Patient|null $patient */
        $patient = $visit->patient;
        /** @var Person|null $person */
        $person = $patient?->person;
        /** @var ClinicalAssessment|null $assessment */
        $assessment = $visit->latestAssessment;

        return [
            'visit_number' => $visit->visit_number,
            'arrived_at' => $visit->arrived_at?->toIso8601String(),
            'chief_complaint' => $visit->chief_complaint,
            'patient' => [
                'patient_number' => $patient?->patient_number,
                'name' => $person?->full_name,
                'gender' => $person?->gender,
            ],
            'clinical_assessment' => [
                'working_diagnosis' => $assessment?->working_diagnosis,
                'summary' => $assessment?->assessment_summary,
            ],
            'discharge' => [
                'discharge_type' => $discharge->discharge_type,
                'discharge_destination' => $discharge->discharge_destination,
                'clinical_summary' => $discharge->clinical_summary,
                'final_condition' => $discharge->final_condition,
                'activity_recommendation' => $discharge->activity_recommendation,
                'rest_recommendation' => $discharge->rest_recommendation,
                'restriction_notes' => $discharge->restriction_notes,
                'follow_up_required' => $discharge->follow_up_required,
                'follow_up_summary' => $discharge->follow_up_summary,
                'follow_up_date' => $discharge->follow_up_date?->toIso8601String(),
            ],
        ];
    }
}
