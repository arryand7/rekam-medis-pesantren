<?php

namespace App\Queries\Dashboard;

use App\Models\MedicalVisit;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\Referral;
use App\Models\StockMovement;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ManagementDashboardQuery
{
    /**
     * Get aggregate intelligence metrics for leadership / executive management.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $start = $startDate ?? now()->subDays(29)->startOfDay();
        $end = $endDate ?? now()->endOfDay();

        // Calculate comparison previous period of equal duration
        $daysDiff = (int) max(1, round($start->diffInDays($end) + 1));
        $prevStart = $start->copy()->subDays($daysDiff);
        $prevEnd = $start->copy()->subSecond();

        // Current period aggregates
        $totalVisits = MedicalVisit::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $uniquePatients = MedicalVisit::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->distinct('patient_id')
            ->count('patient_id');

        $totalObservations = ObservationEpisode::whereBetween('created_at', [$start, $end])->count();
        $totalReferrals = Referral::whereBetween('created_at', [$start, $end])->count();
        $totalDischarges = VisitDischarge::whereBetween('created_at', [$start, $end])
            ->where('status', 'finalized')
            ->count();

        $totalFollowUps = VisitFollowUpPlan::whereBetween('created_at', [$start, $end])->count();
        $completedFollowUps = VisitFollowUpPlan::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $followUpCompletionRate = $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100, 1) : 100.0;

        $pharmacyMovements = StockMovement::whereBetween('created_at', [$start, $end])->count();

        // Previous period aggregates for comparison
        $prevVisits = MedicalVisit::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', '!=', 'cancelled')
            ->count();

        $prevObservations = ObservationEpisode::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevReferrals = Referral::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        // Daily trend data points for chart
        $dailyTrends = $this->computeDailyTrends($start, $end);

        return [
            'period' => [
                'start' => $start->format('d M Y'),
                'end' => $end->format('d M Y'),
                'start_raw' => $start->toDateString(),
                'end_raw' => $end->toDateString(),
                'days' => $daysDiff,
            ],
            'total_visits' => $totalVisits,
            'visits_comparison' => $this->calculateComparison($totalVisits, $prevVisits),
            'unique_patients' => $uniquePatients,
            'total_observations' => $totalObservations,
            'observations_comparison' => $this->calculateComparison($totalObservations, $prevObservations),
            'total_referrals' => $totalReferrals,
            'referrals_comparison' => $this->calculateComparison($totalReferrals, $prevReferrals),
            'total_discharges' => $totalDischarges,
            'follow_up_completion_rate' => $followUpCompletionRate,
            'total_follow_ups' => $totalFollowUps,
            'completed_follow_ups' => $completedFollowUps,
            'pharmacy_movements' => $pharmacyMovements,
            'daily_trends' => $dailyTrends,
            'batch_health' => [
                'active' => MedicineBatch::where('expiry_date', '>', now())->where('current_quantity', '>', 0)->count(),
                'near_expiry' => MedicineBatch::whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->where('current_quantity', '>', 0)->count(),
                'expired' => MedicineBatch::where('expiry_date', '<', now()->toDateString())->where('current_quantity', '>', 0)->count(),
                'depleted' => MedicineBatch::where('current_quantity', '<=', 0)->count(),
            ],
        ];
    }

    /**
     * Compute daily series for accessible trend visualization.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function computeDailyTrends(Carbon $start, Carbon $end): Collection
    {
        $trends = collect();
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->endOfDay();

        // Limit data points to maximum 31 days to keep visualization lightweight and legible
        $stepDays = $start->diffInDays($end) > 31 ? (int) ceil($start->diffInDays($end) / 30) : 1;

        while ($cursor->lte($endDay)) {
            $bucketStart = $cursor->copy();
            $bucketEnd = $cursor->copy()->addDays($stepDays)->subSecond();
            if ($bucketEnd->gt($endDay)) {
                $bucketEnd = $endDay->copy();
            }

            $visitCount = MedicalVisit::whereBetween('created_at', [$bucketStart, $bucketEnd])
                ->where('status', '!=', 'cancelled')
                ->count();

            $obsCount = ObservationEpisode::whereBetween('created_at', [$bucketStart, $bucketEnd])->count();
            $refCount = Referral::whereBetween('created_at', [$bucketStart, $bucketEnd])->count();

            $trends->push([
                'label' => $stepDays === 1 ? $bucketStart->format('d M') : $bucketStart->format('d M').' - '.$bucketEnd->format('d M'),
                'date' => $bucketStart->toDateString(),
                'visits' => $visitCount,
                'observations' => $obsCount,
                'referrals' => $refCount,
            ]);

            $cursor->addDays($stepDays);
        }

        return $trends;
    }

    /**
     * Compute mathematically safe comparison indicator.
     *
     * @return array<string, mixed>
     */
    protected function calculateComparison(int $current, int $previous): array
    {
        if ($previous === 0) {
            return [
                'has_comparison' => false,
                'label' => 'Tidak tersedia pembanding',
                'change_pct' => null,
                'direction' => 'neutral',
            ];
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        return [
            'has_comparison' => true,
            'label' => ($change >= 0 ? '+'.$change : $change).'% dari periode sebelumnya',
            'change_pct' => $change,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
