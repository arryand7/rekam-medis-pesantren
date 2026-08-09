<?php

namespace App\Services\Dashboard;

use App\Models\IntegrationOutboxEvent;
use App\Models\MedicalVisit;
use App\Models\MedicationOrder;
use App\Models\ObservationEpisode;
use App\Models\Referral;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;

/**
 * Service providing real-time clinical workflow metrics for medical staff.
 */
class ClinicalDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return [
            'visits_today' => MedicalVisit::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'waiting_assessment' => MedicalVisit::whereIn('status', ['registered', 'waiting_assessment'])->count(),
            'under_observation' => ObservationEpisode::where('status', 'active')->count(),
            'referral_external' => Referral::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'follow_up_due' => VisitFollowUpPlan::where('status', 'planned')
                ->where('due_at', '<=', now()->endOfDay())
                ->count(),
            'discharges_today' => VisitDischarge::where('status', 'finalized')
                ->whereBetween('finalized_at', [$todayStart, $todayEnd])
                ->count(),
            'pending_medications' => MedicationOrder::where('status', 'active')->count(),
            'integration_failures' => IntegrationOutboxEvent::whereIn('status', ['failed', 'dead_letter'])->count(),
        ];
    }
}
