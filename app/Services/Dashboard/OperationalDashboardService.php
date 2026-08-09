<?php

namespace App\Services\Dashboard;

use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\OperationalNotification;
use App\Models\Patient;
use App\Models\Person;
use App\Models\VisitDischarge;

/**
 * Service providing dorm and homeroom operational views (accommodations, active restrictions, acknowledgments)
 * with strict exclusion of clinical diagnosis/narrative.
 */
class OperationalDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getOperationalOverview(): array
    {
        $activeRestrictions = ActivityRestriction::with(['visitDischarge.medicalVisit.patient.person'])
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->latest('effective_start')
            ->get()
            ->map(function (ActivityRestriction $restriction): array {
                $visitDischarge = $restriction->visitDischarge;
                $medicalVisit = $visitDischarge instanceof VisitDischarge ? $visitDischarge->medicalVisit : null;
                $patient = $medicalVisit instanceof MedicalVisit ? $medicalVisit->patient : null;
                $person = $patient instanceof Patient ? $patient->person : null;
                $personName = $person instanceof Person ? $person->full_name : 'Santri/Warga';

                return [
                    'id' => $restriction->id,
                    'person_name' => $personName,
                    'activity_status' => $restriction->activity_status,
                    'restriction_type' => $restriction->restriction_type,
                    'effective_start' => $restriction->effective_start->format('d M Y H:i'),
                    'effective_until' => $restriction->effective_until ? $restriction->effective_until->format('d M Y H:i') : 'Selesai evaluasi',
                    'practical_notes' => $restriction->restriction_notes,
                    'allowed_activity' => $restriction->allowed_activity_notes ?? 'Aktivitas ringan wajar',
                ];
            });

        $pendingNotifications = OperationalNotification::with(['person'])
            ->where('status', 'prepared')
            ->latest('prepared_at')
            ->limit(20)
            ->get()
            ->map(function (OperationalNotification $notif): array {
                $person = $notif->person;
                $personName = $person instanceof Person ? $person->full_name : 'Santri/Warga';

                return [
                    'id' => $notif->id,
                    'person_name' => $personName,
                    'recipient_type' => $notif->recipient_type,
                    'notification_type' => $notif->notification_type,
                    'priority' => $notif->priority,
                    'prepared_at' => $notif->prepared_at->format('d M Y H:i'),
                ];
            });

        return [
            'active_restrictions' => $activeRestrictions,
            'pending_notifications' => $pendingNotifications,
            'active_restrictions_count' => $activeRestrictions->count(),
            'pending_notifications_count' => $pendingNotifications->count(),
        ];
    }
}
