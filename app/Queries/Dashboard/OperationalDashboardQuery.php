<?php

namespace App\Queries\Dashboard;

use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\OperationalNotification;
use App\Models\Patient;
use App\Models\Person;
use App\Models\VisitDischarge;
use Illuminate\Support\Collection;

class OperationalDashboardQuery
{
    /**
     * Get operational summary for dormitory supervisors & homeroom teachers.
     *
     * @return array<string, mixed>
     */
    public function getOverview(): array
    {
        $activeRestrictions = $this->getActiveRestrictions();
        $pendingNotifications = $this->getPendingNotifications();

        return [
            'active_restrictions' => $activeRestrictions,
            'pending_notifications' => $pendingNotifications,
            'active_restrictions_count' => $activeRestrictions->count(),
            'pending_notifications_count' => $pendingNotifications->count(),
            'total_active_restrictions' => $activeRestrictions->count(),
            'total_pending_acknowledgements' => $pendingNotifications->count(),
            'total_bed_rest' => $activeRestrictions->where('activity_status', 'bed_rest')->count(),
            'total_physical_restricted' => $activeRestrictions->where('activity_status', 'light_duty')->count(),
        ];
    }

    /**
     * Active restrictions list for dorm supervisors and homeroom teachers.
     */
    public function getActiveRestrictions(): Collection
    {
        // 1. Check dedicated ActivityRestriction records if populated
        $restrictions = ActivityRestriction::with('visitDischarge.medicalVisit.patient.person')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->latest('effective_start')
            ->get();

        if ($restrictions->isNotEmpty()) {
            return $restrictions->map(function (ActivityRestriction $res): array {
                /** @var VisitDischarge|null $discharge */
                $discharge = $res->visitDischarge;
                /** @var MedicalVisit|null $visit */
                $visit = $discharge?->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;

                return [
                    'id' => $res->id,
                    'person_name' => $person ? $person->name : 'Santri/Warga',
                    'activity_status' => $res->activity_status,
                    'restriction_type' => $res->restriction_type,
                    'practical_notes' => $res->restriction_notes ?? 'Mengikuti anjuran istirahat Poskestren.',
                    'allowed_activity' => $res->allowed_activity_notes ?? 'Aktivitas ringan mandiri.',
                    'effective_start' => $res->effective_start->format('d M Y H:i'),
                    'effective_until' => $res->effective_until ? $res->effective_until->format('d M Y H:i') : 'Selesai Evaluasi',
                ];
            });
        }

        // 2. Fallback to active finalized visit discharges with rest/activity recommendations
        return VisitDischarge::with('medicalVisit.patient.person')
            ->where('status', 'finalized')
            ->where('activity_recommendation', '!=', 'full_activity')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(function (VisitDischarge $discharge): array {
                /** @var MedicalVisit|null $visit */
                $visit = $discharge->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;

                return [
                    'id' => $discharge->id,
                    'person_name' => $person ? $person->name : 'Santri/Warga',
                    'activity_status' => $discharge->activity_recommendation,
                    'restriction_type' => $discharge->rest_recommendation ?? 'istirahat_asrama',
                    'practical_notes' => $discharge->restriction_notes ?? 'Mengikuti anjuran istirahat dokter Poskestren.',
                    'allowed_activity' => 'Aktivitas belajar mandiri ringan di asrama.',
                    'effective_start' => $discharge->finalized_at ? $discharge->finalized_at->format('d M Y H:i') : $discharge->created_at->format('d M Y H:i'),
                    'effective_until' => $discharge->follow_up_date ? $discharge->follow_up_date->format('d M Y H:i') : 'Selesai Evaluasi',
                ];
            });
    }

    /**
     * Unacknowledged operational handoffs/notifications.
     */
    public function getPendingNotifications(): Collection
    {
        return OperationalNotification::with('person')
            ->whereNull('acknowledged_at')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (OperationalNotification $notif): array {
                /** @var Person|null $person */
                $person = $notif->person;

                return [
                    'id' => $notif->id,
                    'title' => ucwords(str_replace('_', ' ', $notif->notification_type)).($person ? ' - '.$person->name : ''),
                    'message' => 'Pemberitahuan operasional untuk '.$notif->recipient_type,
                    'channel' => $notif->recipient_type,
                    'created_at' => $notif->created_at ? $notif->created_at->diffForHumans() : '-',
                    'action_url' => route('notifications.operational.index'),
                ];
            });
    }
}
