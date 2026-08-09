<?php

namespace App\Services\Dashboard;

use App\Models\MedicalVisit;
use App\Models\Medicine;
use App\Models\ObservationEpisode;
use App\Models\Referral;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;
use Carbon\Carbon;

/**
 * Service providing high-level aggregated metrics for school management without patient-level medical details.
 */
class ManagementDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getAggregatedMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $start = $startDate ?? now()->subDays(30)->startOfDay();
        $end = $endDate ?? now()->endOfDay();

        $totalVisits = MedicalVisit::whereBetween('created_at', [$start, $end])->count();
        $observationCount = ObservationEpisode::whereBetween('created_at', [$start, $end])->count();
        $referralCount = Referral::whereBetween('created_at', [$start, $end])->count();
        $dischargeCount = VisitDischarge::whereBetween('created_at', [$start, $end])
            ->where('status', 'finalized')
            ->count();

        $totalFollowUps = VisitFollowUpPlan::whereBetween('created_at', [$start, $end])->count();
        $completedFollowUps = VisitFollowUpPlan::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $followUpCompletionRate = $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100, 1) : 100.0;

        $lowStockMedicines = Medicine::where('is_active', true)
            ->whereHas('batches', function ($query) {
                $query->where('current_quantity', '<=', 10);
            })
            ->count();

        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'total_visits' => $totalVisits,
            'total_observations' => $observationCount,
            'total_referrals' => $referralCount,
            'total_discharges' => $dischargeCount,
            'follow_up_completion_rate' => $followUpCompletionRate,
            'low_stock_medicines_count' => $lowStockMedicines,
        ];
    }
}
