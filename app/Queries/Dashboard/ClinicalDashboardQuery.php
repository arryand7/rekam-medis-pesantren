<?php

namespace App\Queries\Dashboard;

use App\Models\ClinicalConsultation;
use App\Models\HealthcarePartner;
use App\Models\IntegrationOutboxEvent;
use App\Models\MedicalVisit;
use App\Models\MedicationOrder;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClinicalDashboardQuery
{
    /**
     * Get aggregate KPI metrics for clinical staff.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(?Carbon $date = null): array
    {
        $targetDate = $date ?? now();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        return [
            'visits_today' => MedicalVisit::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', '!=', 'cancelled')
                ->count(),
            'waiting_assessment' => MedicalVisit::whereIn('status', ['registered', 'waiting_assessment'])->count(),
            'under_observation' => ObservationEpisode::where('status', 'active')->count(),
            'referral_active' => Referral::whereIn('status', [
                'prepared', 'approved', 'ready_to_depart', 'departed',
                'arrived', 'accepted', 'under_external_care', 'returned',
            ])->count(),
            'pending_consultations' => ClinicalConsultation::where('status', 'responded')
                ->whereNull('completed_at')
                ->count(),
            'follow_up_due' => VisitFollowUpPlan::where('status', 'planned')
                ->where('due_at', '<=', now()->endOfDay())
                ->count(),
            'discharges_today' => VisitDischarge::where('status', 'finalized')
                ->whereBetween('finalized_at', [$startOfDay, $endOfDay])
                ->count(),
            'pending_medications' => MedicationOrder::where('status', 'active')->count(),
            'integration_failures' => IntegrationOutboxEvent::whereIn('status', ['failed', 'dead_letter'])->count(),
        ];
    }

    /**
     * Work Queue 1: Patients waiting for initial clinical assessment.
     */
    public function getWaitingAssessmentQueue(int $limit = 10): Collection
    {
        return MedicalVisit::with(['patient.person', 'receivingOfficer'])
            ->whereIn('status', ['registered', 'waiting_assessment'])
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function (MedicalVisit $visit): array {
                /** @var Patient|null $patient */
                $patient = $visit->patient;
                /** @var Person|null $person */
                $person = $patient?->person;

                return [
                    'visit_id' => $visit->id,
                    'visit_number' => $visit->visit_number,
                    'patient_name' => $person ? $person->name : 'Santri/Pasien',
                    'mrn' => $patient ? $patient->patient_number : '-',
                    'arrived_at' => $visit->arrived_at ?? $visit->created_at,
                    'waiting_time' => ($visit->arrived_at ?? $visit->created_at)?->diffForHumans(),
                    'chief_complaint' => $visit->chief_complaint,
                    'status' => $visit->status,
                    'action_url' => route('visits.assessment', $visit->id),
                ];
            });
    }

    /**
     * Work Queue 2: Patients currently under active observation.
     */
    public function getActiveObservationQueue(int $limit = 10): Collection
    {
        return ObservationEpisode::with(['medicalVisit.patient.person', 'responsibleOfficer'])
            ->where('status', 'active')
            ->orderBy('next_monitoring_due_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function (ObservationEpisode $obs): array {
                /** @var MedicalVisit|null $visit */
                $visit = $obs->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;

                /** @var User|null $officer */
                $officer = $obs->responsibleOfficer;

                return [
                    'observation_id' => $obs->id,
                    'visit_id' => $visit?->id,
                    'visit_number' => $visit?->visit_number,
                    'patient_name' => $person ? $person->name : 'Santri/Pasien',
                    'bed_label' => $obs->bed_label ?? 'Ruang Observasi',
                    'started_at' => $obs->started_at,
                    'next_monitoring_due_at' => $obs->next_monitoring_due_at,
                    'monitoring_interval' => $obs->monitoring_interval_minutes,
                    'responsible_officer' => $officer ? $officer->name : 'Petugas Jaga',
                    'action_url' => route('observations.show', $obs->id),
                ];
            });
    }

    /**
     * Work Queue 3: External advice received, awaiting local clinical decision.
     */
    public function getPendingConsultationDecisionQueue(int $limit = 10): Collection
    {
        return ClinicalConsultation::with(['medicalVisit.patient.person', 'healthcarePartner'])
            ->where('status', 'responded')
            ->whereNull('completed_at')
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function (ClinicalConsultation $consultation): array {
                /** @var MedicalVisit|null $visit */
                $visit = $consultation->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var HealthcarePartner|null $partner */
                $partner = $consultation->healthcarePartner;

                return [
                    'consultation_id' => $consultation->id,
                    'visit_id' => $visit?->id,
                    'visit_number' => $visit?->visit_number,
                    'patient_name' => $person ? $person->name : 'Santri/Pasien',
                    'partner_name' => $partner ? $partner->name : 'Faskes Mitra',
                    'urgency' => $consultation->urgency,
                    'responded_at' => $consultation->updated_at,
                    'action_url' => route('consultations.show', $consultation->id),
                ];
            });
    }

    /**
     * Work Queue 4: Active external referrals and referrals returned awaiting medical review.
     */
    public function getReferralFollowUpQueue(int $limit = 10): Collection
    {
        return Referral::with(['medicalVisit.patient.person', 'healthcarePartner'])
            ->whereIn('status', [
                'prepared', 'approved', 'ready_to_depart', 'departed',
                'arrived', 'accepted', 'under_external_care', 'returned',
            ])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (Referral $ref): array {
                /** @var MedicalVisit|null $visit */
                $visit = $ref->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var HealthcarePartner|null $partner */
                $partner = $ref->healthcarePartner;

                return [
                    'referral_id' => $ref->id,
                    'referral_number' => $ref->referral_number,
                    'visit_id' => $visit?->id,
                    'visit_number' => $visit?->visit_number,
                    'patient_name' => $person ? $person->name : 'Santri/Pasien',
                    'partner_name' => $partner ? $partner->name : 'RS/Faskes Tujuan',
                    'status' => $ref->status,
                    'urgency' => $ref->urgency,
                    'updated_at' => $ref->updated_at,
                    'action_url' => route('referrals.show', $ref->id),
                ];
            });
    }

    /**
     * Work Queue 5: Follow-up visits and control tasks due today or overdue.
     */
    public function getDueFollowUpQueue(int $limit = 10): Collection
    {
        return VisitFollowUpPlan::with(['visitDischarge.medicalVisit.patient.person', 'healthcarePartner'])
            ->where('status', 'planned')
            ->where('due_at', '<=', now()->endOfDay())
            ->orderBy('due_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function (VisitFollowUpPlan $plan): array {
                /** @var VisitDischarge|null $discharge */
                $discharge = $plan->visitDischarge;
                /** @var MedicalVisit|null $visit */
                $visit = $discharge?->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var HealthcarePartner|null $partner */
                $partner = $plan->healthcarePartner;

                return [
                    'plan_id' => $plan->id,
                    'visit_id' => $visit?->id,
                    'visit_number' => $visit?->visit_number,
                    'patient_name' => $person ? $person->name : 'Santri/Pasien',
                    'follow_up_type' => $plan->follow_up_type,
                    'due_at' => $plan->due_at,
                    'instructions' => $plan->instructions,
                    'partner_name' => $partner ? $partner->name : null,
                    'is_overdue' => $plan->due_at ? $plan->due_at->isPast() : false,
                    'action_url' => $visit ? route('discharges.workspace', ['visit_id' => $visit->id]) : '#',
                ];
            });
    }
}
