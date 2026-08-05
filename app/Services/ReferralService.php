<?php

namespace App\Services;

use App\Models\ClinicalAssessment;
use App\Models\ClinicalConsultation;
use App\Models\HealthcarePartner;
use App\Models\HealthcarePartnerContact;
use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\ReferralCompanion;
use App\Models\ReferralHandover;
use App\Models\ReferralReturn;
use App\Models\ReferralReturnReview;
use App\Models\ReferralStatusEvent;
use App\Models\ReferralTransport;
use App\Models\ReferralVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Create a new referral for a medical visit.
     *
     * Enforces:
     * - finalized assessment required
     * - partner active + referral_enabled
     * - one active referral guard (concurrency-safe via pessimistic lock on medical_visit)
     * - server-generated referral number (ULID-based, concurrency-safe)
     * - emergency referral does NOT require existing consultation
     */
    public function createReferral(MedicalVisit $visit, array $data, ?User $actor = null): Referral
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            // Pessimistic lock on medical visit to guard concurrent referral creation
            $lockedVisit = MedicalVisit::lockForUpdate()->findOrFail($visit->id);

            if ($lockedVisit->status === 'cancelled') {
                throw new Exception('Kunjungan medis sudah dibatalkan, tidak dapat membuat rujukan baru.');
            }

            /** @var ClinicalAssessment|null $assessment */
            $assessment = $lockedVisit->latestAssessment;
            if (! $assessment || $assessment->status !== 'finalized') {
                throw new Exception('Rujukan memerlukan pengkajian klinis yang telah difinalisasi.');
            }

            // One-active-referral guard
            $activeReferral = Referral::where('medical_visit_id', $lockedVisit->id)
                ->whereNotIn('status', ['cancelled', 'completed', 'entered_in_error', 'superseded', 'declined_by_destination'])
                ->lockForUpdate()
                ->first();

            if ($activeReferral && empty($data['supersedes_referral_id'])) {
                throw new Exception('Kunjungan ini sudah memiliki rujukan aktif. Batalkan atau supersede rujukan yang ada terlebih dahulu.');
            }

            $partner = HealthcarePartner::where('id', $data['healthcare_partner_id'])
                ->where('is_active', true)
                ->where('referral_enabled', true)
                ->firstOrFail();

            $contact = ! empty($data['recipient_contact_id'])
                ? HealthcarePartnerContact::where('id', $data['recipient_contact_id'])->first()
                : null;

            // Generate concurrency-safe referral number, retry on unlikely collision
            $referralNumber = null;
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $candidate = Referral::generateReferralNumber();
                if (! Referral::where('referral_number', $candidate)->exists()) {
                    $referralNumber = $candidate;
                    break;
                }
            }
            if (! $referralNumber) {
                throw new Exception('Gagal membuat nomor rujukan unik. Coba lagi.');
            }

            /** @var Patient $patient */
            $patient = $lockedVisit->patient;
            /** @var Person $person */
            $person = $patient->person;

            $referral = Referral::create([
                'medical_visit_id' => $lockedVisit->id,
                'clinical_assessment_id' => $assessment->id,
                'observation_episode_id' => $data['observation_episode_id'] ?? null,
                'clinical_consultation_id' => $data['clinical_consultation_id'] ?? null,
                'consultation_local_decision_id' => $data['consultation_local_decision_id'] ?? null,
                'healthcare_partner_id' => $partner->id,
                'recipient_contact_id' => $contact?->id,
                'referral_number' => $referralNumber,
                'urgency' => $data['urgency'],
                'reason' => trim($data['reason']),
                'clinical_summary' => trim($data['clinical_summary']),
                'requested_service_or_department' => $data['requested_service_or_department'] ?? null,
                'status' => 'prepared',
                'initiated_by_id' => $actor?->id,
                'initiated_at' => now(),
            ]);

            // Superseding: mark the old referral as superseded
            if (! empty($data['supersedes_referral_id'])) {
                Referral::where('id', $data['supersedes_referral_id'])
                    ->where('medical_visit_id', $lockedVisit->id)
                    ->update(['status' => 'superseded']);
            }

            // Mark any pending consultation as superseded_by_referral
            if (! empty($data['clinical_consultation_id'])) {
                ClinicalConsultation::where('id', $data['clinical_consultation_id'])
                    ->whereIn('status', ['ready', 'sent', 'acknowledged'])
                    ->update(['status' => 'superseded_by_referral']);
            }

            // Build version 1 snapshot
            $summaryPayload = $this->buildSummaryPayload($referral, $lockedVisit, $patient, $person, $assessment, $partner);
            $checksum = hash('sha256', (string) json_encode($summaryPayload));

            ReferralVersion::create([
                'referral_id' => $referral->id,
                'version_number' => 1,
                'summary_payload' => $summaryPayload,
                'checksum' => $checksum,
                'authored_by_id' => $actor?->id,
                'finalized_at' => now(),
                'redaction_notes' => $data['redaction_notes'] ?? null,
            ]);

            // Update visit status
            $lockedVisit->update(['status' => 'referral_prepared']);

            AuditLogService::log(
                action: 'referral.created',
                subjectType: 'Referral',
                subjectId: $referral->id,
                before: null,
                after: $referral->toArray(),
                reason: "Pembuatan rujukan {$referral->referral_number} ke {$partner->name} ({$referral->urgency}): {$referral->reason}"
            );

            return $referral;
        });
    }

    /**
     * Arrange transport for a referral.
     */
    public function arrangeTransport(Referral $referral, array $data, ?User $actor = null): ReferralTransport
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data, $actor) {
            $transport = ReferralTransport::create([
                'referral_id' => $referral->id,
                'transport_type' => $data['transport_type'] ?? 'school_vehicle',
                'vehicle_identifier' => $data['vehicle_identifier'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_contact' => $data['driver_contact'] ?? null,
                'arranged_by_id' => $actor?->id,
                'arranged_at' => now(),
                'departure_planned' => $data['departure_planned'] ?? null,
                'status' => 'planned',
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLogService::log(
                action: 'referral_transport.arranged',
                subjectType: 'ReferralTransport',
                subjectId: $transport->id,
                before: null,
                after: $transport->toArray(),
                reason: "Pengaturan transportasi rujukan {$referral->referral_number}: {$transport->transport_type}"
            );

            return $transport;
        });
    }

    /**
     * Assign a companion to a referral.
     * Primary companion is unique per active referral.
     */
    public function assignCompanion(Referral $referral, array $data, ?User $actor = null): ReferralCompanion
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data, $actor) {
            $isPrimary = (bool) ($data['is_primary'] ?? true);

            // Enforce unique primary companion
            if ($isPrimary) {
                $existingPrimary = ReferralCompanion::where('referral_id', $referral->id)
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if ($existingPrimary) {
                    throw new Exception('Rujukan ini sudah memiliki pendamping utama aktif. Nonaktifkan pendamping sebelumnya terlebih dahulu.');
                }
            }

            $companion = ReferralCompanion::create([
                'referral_id' => $referral->id,
                'user_id' => $data['user_id'] ?? null,
                'name_snapshot' => trim($data['name_snapshot']),
                'role_relationship' => trim($data['role_relationship']),
                'phone' => $data['phone'] ?? null,
                'is_primary' => $isPrimary,
                'assigned_by_id' => $actor?->id,
                'assigned_at' => now(),
                'status' => 'active',
            ]);

            AuditLogService::log(
                action: 'referral_companion.assigned',
                subjectType: 'ReferralCompanion',
                subjectId: $companion->id,
                before: null,
                after: $companion->toArray(),
                reason: "Penugasan pendamping rujukan {$referral->referral_number}: {$companion->name_snapshot} ({$companion->role_relationship})"
            );

            return $companion;
        });
    }

    /**
     * Record actual departure — sets server-authoritative timestamp.
     */
    public function recordDeparture(Referral $referral, array $data, ?User $actor = null): Referral
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data) {
            if (! in_array($referral->status, ['prepared', 'approved', 'ready_to_depart'])) {
                throw new Exception("Rujukan dengan status '{$referral->status}' tidak dapat berangkat.");
            }

            $departedAt = now(); // always server-authoritative

            $referral->update([
                'status' => 'departed',
                'departed_at' => $departedAt,
            ]);

            // Update transport status
            ReferralTransport::where('referral_id', $referral->id)
                ->where('status', 'planned')
                ->update(['status' => 'departed', 'departure_actual' => $departedAt]);

            // Update visit status
            MedicalVisit::where('id', $referral->medical_visit_id)
                ->update(['status' => 'referred_external']);

            AuditLogService::log(
                action: 'referral.departed',
                subjectType: 'Referral',
                subjectId: $referral->id,
                before: ['status' => 'prepared'],
                after: ['status' => 'departed', 'departed_at' => $departedAt],
                reason: "Keberangkatan rujukan {$referral->referral_number}".($data['emergency_override_reason'] ?? '')
            );

            return $referral->refresh();
        });
    }

    /**
     * Record clinical handoff to destination partner.
     * Idempotent: same idempotency_key returns existing handover.
     */
    public function recordHandover(Referral $referral, array $data, ?User $actor = null): ReferralHandover
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data, $actor) {
            $version = ReferralVersion::where('referral_id', $referral->id)
                ->orderByDesc('version_number')
                ->first();

            if ($version === null) {
                throw new Exception('Rujukan tidak memiliki versi dokumen ringkasan yang siap diserahterimakan.');
            }

            $idempotencyKey = $data['idempotency_key'] ?? ('handoff-'.$referral->id.'-'.$version->version_number);

            // Idempotency check
            $existing = ReferralHandover::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $handover = ReferralHandover::create([
                'referral_id' => $referral->id,
                'referral_version_id' => $version->id,
                'from_user_id' => $actor?->id,
                'destination_partner_id' => $referral->healthcare_partner_id,
                'recipient_contact_id' => $referral->recipient_contact_id,
                'handed_over_at' => now(),
                'notes' => $data['notes'] ?? null,
                'status' => 'handed_over',
                'idempotency_key' => $idempotencyKey,
            ]);

            $partnerName = HealthcarePartner::where('id', $referral->healthcare_partner_id)->value('name') ?? 'Mitra';
            AuditLogService::log(
                action: 'referral_handover.recorded',
                subjectType: 'ReferralHandover',
                subjectId: $handover->id,
                before: null,
                after: $handover->toArray(),
                reason: 'Pencatatan serah terima klinis rujukan '.$referral->referral_number.' ke '.$partnerName
            );

            return $handover;
        });
    }

    /**
     * Record a destination status event (arrived, accepted, declined, etc.)
     * Manual entry default. Handoff ≠ acceptance.
     */
    public function recordStatusEvent(Referral $referral, array $data, ?User $actor = null): ReferralStatusEvent
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data, $actor) {
            $idempotencyKey = $data['idempotency_key'] ?? null;

            if ($idempotencyKey) {
                $existing = ReferralStatusEvent::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $eventType = $data['event_type'];
            $occurredAt = $data['occurred_at'] ?? now();

            $event = ReferralStatusEvent::create([
                'referral_id' => $referral->id,
                'event_type' => $eventType,
                'occurred_at' => $occurredAt,
                'received_at' => now(),
                'source' => $data['source'] ?? 'manual',
                'facility_partner_id' => $referral->healthcare_partner_id,
                'contact_attribution' => $data['contact_attribution'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by_id' => $actor?->id,
                'external_reference' => $data['external_reference'] ?? null,
                'verification_status' => $data['verification_status'] ?? 'unverified',
                'idempotency_key' => $idempotencyKey,
            ]);

            // Update referral status based on event type
            $statusMap = [
                'arrived' => 'arrived',
                'accepted' => 'accepted',
                'declined' => 'declined_by_destination',
                'under_external_care' => 'under_external_care',
                'return_planned' => 'return_planned',
            ];

            if (isset($statusMap[$eventType])) {
                $referral->update([
                    'status' => $statusMap[$eventType],
                    match ($eventType) {
                        'arrived' => 'arrived_at_destination',
                        'accepted' => 'accepted_at_destination',
                        default => 'updated_at',
                    } => now(),
                ]);
            }

            AuditLogService::log(
                action: "referral_status_event.{$eventType}",
                subjectType: 'ReferralStatusEvent',
                subjectId: $event->id,
                before: null,
                after: $event->toArray(),
                reason: "Pembaruan status destinasi rujukan {$referral->referral_number}: {$eventType}"
            );

            return $event;
        });
    }

    /**
     * Record return from referral.
     * Only one return per referral. Only valid from active referral states.
     */
    public function recordReturn(Referral $referral, array $data, ?User $actor = null): ReferralReturn
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referral, $data, $actor) {
            $validStatuses = ['departed', 'arrived', 'accepted', 'under_external_care', 'return_planned'];
            if (! in_array($referral->status, $validStatuses)) {
                throw new Exception("Kepulangan hanya dapat dicatat dari status rujukan aktif (saat ini: {$referral->status}).");
            }

            // One return per referral
            if (ReferralReturn::where('referral_id', $referral->id)->exists()) {
                throw new Exception('Kepulangan dari rujukan sudah pernah dicatat untuk rujukan ini.');
            }

            $returnedAt = now(); // server-authoritative

            $referralReturn = ReferralReturn::create([
                'referral_id' => $referral->id,
                'returned_at' => $returnedAt,
                'recorded_by_id' => $actor?->id,
                'return_transport_notes' => $data['return_transport_notes'] ?? null,
                'accompanied_by_notes' => $data['accompanied_by_notes'] ?? null,
                'external_outcome_summary' => trim($data['external_outcome_summary']),
                'external_diagnosis_text' => $data['external_diagnosis_text'] ?? null,
                'external_procedures_text' => $data['external_procedures_text'] ?? null,
                'external_medication_instructions' => $data['external_medication_instructions'] ?? null,
                'restrictions_text' => $data['restrictions_text'] ?? null,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'follow_up_facility' => $data['follow_up_facility'] ?? null,
                'documents_received_notes' => $data['documents_received_notes'] ?? null,
                'status' => 'returned',
            ]);

            $referral->update([
                'status' => 'returned',
                'returned_at' => $returnedAt,
            ]);

            // Update visit status
            MedicalVisit::where('id', $referral->medical_visit_id)
                ->update(['status' => 'returned_from_referral']);

            AuditLogService::log(
                action: 'referral.returned',
                subjectType: 'ReferralReturn',
                subjectId: $referralReturn->id,
                before: null,
                after: $referralReturn->toArray(),
                reason: "Pencatatan kepulangan dari rujukan {$referral->referral_number}"
            );

            return $referralReturn;
        });
    }

    /**
     * Record local clinical return review.
     *
     * External results do NOT automatically mutate local diagnosis or medication.
     * Local review does NOT create discharge.
     */
    public function recordReturnReview(ReferralReturn $referralReturn, array $data, ?User $actor = null): ReferralReturnReview
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($referralReturn, $data, $actor) {
            $review = ReferralReturnReview::create([
                'referral_return_id' => $referralReturn->id,
                'local_reviewer_id' => $actor?->id,
                'review_summary' => trim($data['review_summary']),
                'decision_type' => $data['decision_type'],
                'medication_reconciliation_note' => $data['medication_reconciliation_note'] ?? null,
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

            // Update referral status to completed, visit to review_completed
            /** @var Referral $referral */
            $referral = $referralReturn->referral;
            $referral->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            MedicalVisit::where('id', $referral->medical_visit_id)
                ->update(['status' => 'referral_review_completed']);

            AuditLogService::log(
                action: 'referral_return_review.finalized',
                subjectType: 'ReferralReturnReview',
                subjectId: $review->id,
                before: null,
                after: $review->toArray(),
                reason: "Peninjauan klinis lokal kepulangan rujukan {$referral->referral_number}: {$review->decision_type}"
            );

            return $review;
        });
    }

    /**
     * Build the versioned summary snapshot payload (minimum necessary).
     */
    private function buildSummaryPayload(
        Referral $referral,
        MedicalVisit $visit,
        Patient $patient,
        Person $person,
        ClinicalAssessment $assessment,
        HealthcarePartner $partner,
    ): array {
        return [
            'referral_number' => $referral->referral_number,
            'urgency' => $referral->urgency,
            'patient' => [
                'patient_number' => $patient->patient_number,
                'name' => $person->name,
                'gender' => $person->gender,
            ],
            'visit' => [
                'visit_number' => $visit->visit_number,
                'arrived_at' => $visit->arrived_at ? (string) $visit->arrived_at : null,
                'chief_complaint' => $visit->chief_complaint,
            ],
            'destination_partner' => $partner->name,
            'requested_service' => $referral->requested_service_or_department,
            'reason' => $referral->reason,
            'clinical_summary' => $referral->clinical_summary,
            'assessment_summary' => $assessment->assessment_summary,
            'working_diagnosis' => $assessment->working_diagnosis,
            'active_allergies' => $patient->activeAllergies->pluck('allergen')->toArray(),
            'latest_vital_signs' => $visit->latestVitalSign?->toArray(),
        ];
    }

    /**
     * Test-only helper: quickly register a healthcare partner with referral_enabled=true.
     * Do NOT use in production flows. Use ClinicalConsultationService::createPartner() instead.
     */
    public function createPartnerForTest(array $data, ?User $actor = null): HealthcarePartner
    {
        return HealthcarePartner::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'partner_type' => $data['partner_type'] ?? 'hospital',
            'is_active' => true,
            'consultation_enabled' => true,
            'referral_enabled' => true,
            'default_channel' => 'fake_transport',
            'created_by_id' => $actor?->id,
        ]);
    }
}
