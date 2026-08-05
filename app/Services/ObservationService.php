<?php

namespace App\Services;

use App\Models\MedicalVisit;
use App\Models\ObservationEpisode;
use App\Models\ObservationHandover;
use App\Models\ObservationRecord;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObservationService
{
    /**
     * Start a new observation episode for a medical visit inside a DB transaction.
     */
    public function startEpisode(MedicalVisit $visit, array $data, ?User $actor = null): ObservationEpisode
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            // Lock medical visit row to prevent duplicate active observations
            $lockedVisit = MedicalVisit::where('id', $visit->id)->lockForUpdate()->firstOrFail();

            // Active observation guard check
            $activeEpisode = ObservationEpisode::where('medical_visit_id', $lockedVisit->id)
                ->whereIn('status', ['planned', 'active'])
                ->lockForUpdate()
                ->first();

            if ($activeEpisode) {
                throw new Exception("Kunjungan {$lockedVisit->visit_number} sudah memiliki episode observasi aktif.");
            }

            $interval = isset($data['monitoring_interval_minutes']) ? (int) $data['monitoring_interval_minutes'] : 120;
            $nextDue = now()->addMinutes($interval);

            $episode = ObservationEpisode::create([
                'medical_visit_id' => $lockedVisit->id,
                'reason' => $data['reason'],
                'status' => 'active',
                'started_at' => now(),
                'started_by_id' => $actor?->id,
                'responsible_officer_id' => $actor?->id,
                'location_label' => $data['location_label'] ?? 'Ruang Observasi Poskestren',
                'bed_label' => $data['bed_label'] ?? null,
                'monitoring_interval_minutes' => $interval,
                'next_monitoring_due_at' => $nextDue,
            ]);

            // Transition visit status to under_observation
            $lockedVisit->update(['status' => 'under_observation']);

            AuditLogService::log(
                action: 'observation.started',
                subjectType: 'ObservationEpisode',
                subjectId: $episode->id,
                before: null,
                after: $episode->toArray(),
                reason: "Memulai episode observasi Poskestren di {$episode->location_label}"
            );

            AuditLogService::log(
                action: 'medical_visit.entered_observation',
                subjectType: 'MedicalVisit',
                subjectId: $lockedVisit->id,
                before: ['status' => $lockedVisit->getOriginal('status')],
                after: ['status' => 'under_observation'],
                reason: 'Kunjungan berpindah ke status observasi'
            );

            return $episode;
        });
    }

    /**
     * Add a periodic monitoring record to an active observation episode.
     */
    public function recordMonitoring(ObservationEpisode $episode, array $data, ?User $actor = null): ObservationRecord
    {
        $actor = $actor ?? Auth::user();

        if (! $episode->isActive()) {
            throw new Exception("Episode observasi {$episode->id} sudah tidak aktif.");
        }

        return DB::transaction(function () use ($episode, $data, $actor) {
            $record = ObservationRecord::create([
                'observation_episode_id' => $episode->id,
                'recorded_at' => now(),
                'recorded_by_id' => $actor?->id,
                'condition_summary' => $data['condition_summary'],
                'symptom_changes' => $data['symptom_changes'] ?? null,
                'general_condition' => $data['general_condition'] ?? 'good',
                'vital_sign_id' => $data['vital_sign_id'] ?? null,
                'fluid_intake_note' => $data['fluid_intake_note'] ?? null,
                'food_intake_note' => $data['food_intake_note'] ?? null,
                'elimination_note' => $data['elimination_note'] ?? null,
                'activity_or_rest_note' => $data['activity_or_rest_note'] ?? null,
                'follow_up_note' => $data['follow_up_note'] ?? null,
                'status' => 'finalized',
                'finalized_at' => now(),
                'finalized_by_id' => $actor?->id,
            ]);

            // Reset next monitoring due time
            $interval = $episode->monitoring_interval_minutes ?? 120;
            $episode->update([
                'next_monitoring_due_at' => now()->addMinutes($interval),
            ]);

            AuditLogService::log(
                action: 'observation_monitoring.recorded',
                subjectType: 'ObservationRecord',
                subjectId: $record->id,
                before: null,
                after: $record->toArray(),
                reason: "Pencatatan pemantauan berkala observasi: {$record->condition_summary}"
            );

            return $record;
        });
    }

    /**
     * Submit shift handover for an active observation episode.
     */
    public function submitHandover(ObservationEpisode $episode, array $data, ?User $actor = null): ObservationHandover
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($episode, $data, $actor) {
            $handover = ObservationHandover::create([
                'observation_episode_id' => $episode->id,
                'from_user_id' => $actor?->id,
                'to_user_id' => $data['to_user_id'] ?? null,
                'prepared_at' => now(),
                'summary' => $data['summary'],
                'current_condition' => $data['current_condition'],
                'pending_tasks' => $data['pending_tasks'] ?? null,
                'risks_or_warnings' => $data['risks_or_warnings'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            AuditLogService::log(
                action: 'observation_handover.submitted',
                subjectType: 'ObservationHandover',
                subjectId: $handover->id,
                before: null,
                after: $handover->toArray(),
                reason: 'Pengajuan serah terima tugas jaga (shift handover)'
            );

            return $handover;
        });
    }

    /**
     * Acknowledge shift handover with atomic transfer of responsible officer.
     */
    public function acknowledgeHandover(ObservationHandover $handover, ?User $actor = null): ObservationHandover
    {
        $actor = $actor ?? Auth::user();

        if ($handover->status === 'acknowledged') {
            throw new Exception("Handover {$handover->id} sudah disetujui sebelumnya.");
        }

        return DB::transaction(function () use ($handover, $actor) {
            $episode = ObservationEpisode::where('id', $handover->observation_episode_id)->lockForUpdate()->firstOrFail();
            $oldOfficerId = $episode->responsible_officer_id;

            $handover->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'acknowledged_by_id' => $actor?->id,
            ]);

            // ATOMIC RESPONSIBILITY TRANSFER
            $episode->update([
                'responsible_officer_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'observation_handover.acknowledged',
                subjectType: 'ObservationHandover',
                subjectId: $handover->id,
                before: ['responsible_officer_id' => $oldOfficerId],
                after: ['responsible_officer_id' => $actor?->id],
                reason: 'Konfirmasi serah terima tugas jaga & pengalihan penanggung jawab episode observasi'
            );

            return $handover;
        });
    }

    /**
     * Complete observation episode with outcome.
     */
    public function completeEpisode(ObservationEpisode $episode, string $outcome, string $outcomeReason, ?User $actor = null): ObservationEpisode
    {
        $actor = $actor ?? Auth::user();

        if ($episode->status === 'completed') {
            throw new Exception("Episode observasi {$episode->id} sudah selesai sebelumnya.");
        }

        return DB::transaction(function () use ($episode, $outcome, $outcomeReason, $actor) {
            $visit = MedicalVisit::where('id', $episode->medical_visit_id)->lockForUpdate()->firstOrFail();

            $episode->update([
                'status' => 'completed',
                'ended_at' => now(),
                'ended_by_id' => $actor?->id,
                'outcome' => $outcome,
                'outcome_reason' => $outcomeReason,
            ]);

            // Transition visit status to observation_completed
            $visit->update(['status' => 'observation_completed']);

            AuditLogService::log(
                action: 'observation.completed',
                subjectType: 'ObservationEpisode',
                subjectId: $episode->id,
                before: ['status' => 'active'],
                after: ['status' => 'completed', 'outcome' => $outcome],
                reason: "Penyelesaian episode observasi dengan outcome: {$outcome}"
            );

            AuditLogService::log(
                action: 'medical_visit.observation_completed',
                subjectType: 'MedicalVisit',
                subjectId: $visit->id,
                before: ['status' => 'under_observation'],
                after: ['status' => 'observation_completed'],
                reason: 'Observasi medis kunjungan selesai'
            );

            return $episode;
        });
    }
}
