<?php

namespace App\Services;

use App\Contracts\ClinicalConsultationTransportContract;
use App\Models\ClinicalAssessment;
use App\Models\ClinicalConsultation;
use App\Models\ClinicalConsultationTransmission;
use App\Models\ClinicalConsultationVersion;
use App\Models\ConsultationLocalDecision;
use App\Models\ExternalClinicalAdvice;
use App\Models\HealthcarePartner;
use App\Models\HealthcarePartnerContact;
use App\Models\MedicalVisit;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\Transport\FakeClinicalConsultationTransport;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicalConsultationService
{
    protected ClinicalConsultationTransportContract $transport;

    public function __construct(?ClinicalConsultationTransportContract $transport = null)
    {
        $this->transport = $transport ?? new FakeClinicalConsultationTransport;
    }

    /**
     * Register a new healthcare partner facility (Puskesmas / Rumah Sakit / Klinik).
     */
    public function createPartner(array $data, ?User $actor = null): HealthcarePartner
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            $partner = HealthcarePartner::create([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'partner_type' => $data['partner_type'] ?? 'puskesmas',
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'official_email' => $data['official_email'] ?? null,
                'cooperation_reference' => $data['cooperation_reference'] ?? null,
                'is_active' => true,
                'consultation_enabled' => true,
                'referral_enabled' => true,
                'default_channel' => $data['default_channel'] ?? 'fake_transport',
                'created_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'healthcare_partner.created',
                subjectType: 'HealthcarePartner',
                subjectId: $partner->id,
                before: null,
                after: $partner->toArray(),
                reason: "Pendaftaran mitra layanan kesehatan baru: {$partner->name} ({$partner->code})"
            );

            return $partner;
        });
    }

    /**
     * Create healthcare partner clinician/contact point.
     */
    public function createPartnerContact(HealthcarePartner $partner, array $data, ?User $actor = null): HealthcarePartnerContact
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($partner, $data, $actor) {
            $contact = HealthcarePartnerContact::create([
                'healthcare_partner_id' => $partner->id,
                'name' => trim($data['name']),
                'profession' => trim($data['profession']),
                'registration_identifier' => $data['registration_identifier'] ?? null,
                'department' => $data['department'] ?? null,
                'official_contact' => $data['official_contact'] ?? null,
                'channel_type' => $data['channel_type'] ?? 'fake_transport',
                'is_active' => true,
                'verified_at' => now(),
                'verified_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'healthcare_partner_contact.created',
                subjectType: 'HealthcarePartnerContact',
                subjectId: $contact->id,
                before: null,
                after: $contact->toArray(),
                reason: "Pendaftaran kontak medis mitra: {$contact->name} ({$contact->profession})"
            );

            return $contact;
        });
    }

    /**
     * Create external clinical consultation request with versioned snapshot summary.
     */
    public function createConsultation(MedicalVisit $visit, array $data, ?User $actor = null): ClinicalConsultation
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            /** @var ClinicalAssessment|null $assessment */
            $assessment = $visit->latestAssessment;
            if (! $assessment) {
                throw new Exception('Konsultasi eksternal memerlukan pengkajian klinis medis yang telah difinalisasi.');
            }

            /** @var Patient $patient */
            $patient = $visit->patient;
            /** @var Person $person */
            $person = $patient->person;
            /** @var ObservationEpisode|null $activeObs */
            $activeObs = $visit->activeObservationEpisode;

            $partner = HealthcarePartner::where('id', $data['healthcare_partner_id'])->where('is_active', true)->firstOrFail();
            $contact = ! empty($data['recipient_contact_id']) ? HealthcarePartnerContact::where('id', $data['recipient_contact_id'])->first() : null;

            $urgency = $data['urgency'] ?? 'routine';

            $consultation = ClinicalConsultation::create([
                'medical_visit_id' => $visit->id,
                'clinical_assessment_id' => $assessment->id,
                'observation_episode_id' => $activeObs?->id,
                'healthcare_partner_id' => $partner->id,
                'recipient_contact_id' => $contact?->id,
                'purpose' => trim($data['purpose']),
                'clinical_question' => trim($data['clinical_question']),
                'urgency' => $urgency,
                'status' => 'ready',
                'created_by_id' => $actor?->id,
                'finalized_at' => now(),
                'finalized_by_id' => $actor?->id,
            ]);

            // Construct Version 1 Snapshot Payload
            $summaryPayload = [
                'consultation_id' => $consultation->id,
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
                'latest_vital_signs' => $visit->latestVitalSign?->toArray(),
                'assessment_summary' => $assessment->assessment_summary,
                'working_diagnosis' => $assessment->working_diagnosis,
                'active_allergies' => $patient->activeAllergies->pluck('allergen')->toArray(),
                'clinical_question' => $consultation->clinical_question,
                'purpose' => $consultation->purpose,
                'urgency' => $consultation->urgency,
            ];

            $checksum = hash('sha256', (string) json_encode($summaryPayload));

            ClinicalConsultationVersion::create([
                'clinical_consultation_id' => $consultation->id,
                'version_number' => 1,
                'summary_payload' => $summaryPayload,
                'checksum' => $checksum,
                'authored_by_id' => $actor?->id,
                'finalized_at' => now(),
                'redaction_notes' => $data['redaction_notes'] ?? null,
            ]);

            // Update visit status
            $visit->update(['status' => 'external_consultation_pending']);

            AuditLogService::log(
                action: 'clinical_consultation.created',
                subjectType: 'ClinicalConsultation',
                subjectId: $consultation->id,
                before: null,
                after: $consultation->toArray(),
                reason: "Pengajuan konsultasi eksternal ke {$partner->name}: {$consultation->purpose}"
            );

            return $consultation;
        });
    }

    /**
     * Transmit consultation request to external healthcare partner using transport abstraction.
     */
    public function transmitConsultation(ClinicalConsultation $consultation, ?User $actor = null): ClinicalConsultationTransmission
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($consultation, $actor) {
            /** @var ClinicalConsultationVersion $version */
            $version = $consultation->latestVersion;
            /** @var HealthcarePartner $partner */
            $partner = $consultation->partner;

            $transmission = $this->transport->transmit($consultation, $version, $actor);

            $consultation->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'clinical_consultation.transmitted',
                subjectType: 'ClinicalConsultationTransmission',
                subjectId: $transmission->id,
                before: ['status' => 'ready'],
                after: $transmission->toArray(),
                reason: "Pengiriman ringkasan konsultasi ke mitra {$partner->name} sukses."
            );

            return $transmission;
        });
    }

    /**
     * Record incoming external clinical advice response.
     */
    public function recordExternalAdvice(ClinicalConsultation $consultation, array $data, ?User $actor = null): ExternalClinicalAdvice
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($consultation, $data, $actor) {
            /** @var HealthcarePartner $partner */
            $partner = $consultation->partner;

            $advice = ExternalClinicalAdvice::create([
                'clinical_consultation_id' => $consultation->id,
                'healthcare_partner_id' => $consultation->healthcare_partner_id,
                'recipient_contact_id' => $consultation->recipient_contact_id,
                'clinician_name' => trim($data['clinician_name']),
                'clinician_profession' => trim($data['clinician_profession']),
                'clinician_identifier' => $data['clinician_identifier'] ?? null,
                'department' => $data['department'] ?? null,
                'responded_at' => $data['responded_at'] ?? now(),
                'received_at' => now(),
                'channel' => $data['channel'] ?? 'fake_transport',
                'advice_text' => trim($data['advice_text']),
                'limitations_text' => $data['limitations_text'] ?? null,
                'recommended_next_step' => $data['recommended_next_step'] ?? null,
                'verification_status' => $data['verification_status'] ?? 'verified',
                'verified_at' => now(),
                'verified_by_id' => $actor?->id,
                'recorded_by_id' => $actor?->id,
                'status' => 'finalized',
            ]);

            $consultation->update(['status' => 'responded']);

            AuditLogService::log(
                action: 'external_clinical_advice.recorded',
                subjectType: 'ExternalClinicalAdvice',
                subjectId: $advice->id,
                before: null,
                after: $advice->toArray(),
                reason: "Pencatatan jawaban konsultasi eksternal dari {$advice->clinician_name} ({$partner->name})"
            );

            return $advice;
        });
    }

    /**
     * Record local clinical decision based on external advice and local assessment.
     */
    public function recordLocalDecision(ClinicalConsultation $consultation, array $data, ?User $actor = null): ConsultationLocalDecision
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($consultation, $data, $actor) {
            /** @var ExternalClinicalAdvice|null $latestAdvice */
            $latestAdvice = $consultation->latestAdvice;

            $decision = ConsultationLocalDecision::create([
                'clinical_consultation_id' => $consultation->id,
                'external_clinical_advice_id' => $data['external_clinical_advice_id'] ?? $latestAdvice?->id,
                'decision_type' => $data['decision_type'],
                'rationale' => trim($data['rationale']),
                'decided_by_id' => $actor?->id,
                'decided_at' => now(),
                'status' => 'finalized',
            ]);

            $consultation->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by_id' => $actor?->id,
            ]);

            /** @var MedicalVisit $visit */
            $visit = $consultation->medicalVisit;
            $visit->update(['status' => 'external_consultation_completed']);

            AuditLogService::log(
                action: 'consultation_local_decision.finalized',
                subjectType: 'ConsultationLocalDecision',
                subjectId: $decision->id,
                before: null,
                after: $decision->toArray(),
                reason: "Penetapan keputusan klinis lokal Poskestren: {$decision->decision_type}"
            );

            return $decision;
        });
    }
}
