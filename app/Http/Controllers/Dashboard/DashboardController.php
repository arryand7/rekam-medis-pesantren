<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Queries\Dashboard\ClinicalDashboardQuery;
use App\Queries\Dashboard\ManagementDashboardQuery;
use App\Queries\Dashboard\OperationalDashboardQuery;
use App\Queries\Dashboard\PharmacyDashboardQuery;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ClinicalDashboardQuery $clinicalQuery,
        protected ManagementDashboardQuery $managementQuery,
        protected OperationalDashboardQuery $operationalQuery,
        protected PharmacyDashboardQuery $pharmacyQuery
    ) {}

    /**
     * Resolve the appropriate role-aware dashboard for authenticated user.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Clinical / Medical staff
        if ($user->hasPermission('view-clinical-dashboard')) {
            return redirect()->route('dashboards.clinical');
        }

        // Pharmacy staff (if dedicated pharmacy view)
        if ($user->hasPermission('view-pharmacy-dashboard')) {
            return redirect()->route('dashboards.pharmacy');
        }

        // Dormitory / Homeroom / Operational staff
        if ($user->hasPermission('view-operational-dashboard')) {
            return redirect()->route('dashboards.operational');
        }

        // Management / Leadership
        if ($user->hasPermission('view-management-dashboard')) {
            return redirect()->route('dashboards.management');
        }

        // Administrator / System Manager
        if ($user->hasPermission('manage-users') || $user->hasPermission('manage-system-settings') || $user->hasRole('admin') || $user->hasRole('administrator')) {
            return view('dashboard');
        }

        // Safe fallback for other authenticated roles
        return view('dashboard');
    }

    /**
     * Clinical work queue and action-oriented dashboard.
     */
    public function clinical(Request $request): View
    {
        $this->authorize('view-clinical-dashboard');

        $metrics = $this->clinicalQuery->getMetrics();
        $waitingAssessmentQueue = $this->clinicalQuery->getWaitingAssessmentQueue(10);
        $activeObservationQueue = $this->clinicalQuery->getActiveObservationQueue(10);
        $pendingConsultationQueue = $this->clinicalQuery->getPendingConsultationDecisionQueue(10);
        $referralFollowUpQueue = $this->clinicalQuery->getReferralFollowUpQueue(10);
        $dueFollowUpQueue = $this->clinicalQuery->getDueFollowUpQueue(10);

        return view('pages.dashboards.clinical', compact(
            'metrics',
            'waitingAssessmentQueue',
            'activeObservationQueue',
            'pendingConsultationQueue',
            'referralFollowUpQueue',
            'dueFollowUpQueue'
        ));
    }

    /**
     * Operational / Dormitory supervisor dashboard with strict minimum-necessary privacy.
     */
    public function operational(Request $request): View
    {
        $this->authorize('view-operational-dashboard');

        $overview = $this->operationalQuery->getOverview();

        return view('pages.dashboards.operational', compact('overview'));
    }

    /**
     * Pharmacy batch health, expiry tracking, and stock movement dashboard.
     */
    public function pharmacy(Request $request): View
    {
        $this->authorize('view-pharmacy-dashboard');

        $metrics = $this->pharmacyQuery->getMetrics();
        $expiringBatches = $this->pharmacyQuery->getExpiringBatches(15);
        $depletedMedicines = $this->pharmacyQuery->getDepletedMedicines(15);
        $recentMovements = $this->pharmacyQuery->getRecentMovements(15);

        return view('pages.dashboards.pharmacy', compact(
            'metrics',
            'expiringBatches',
            'depletedMedicines',
            'recentMovements'
        ));
    }

    /**
     * Management / Executive aggregate intelligence dashboard with date range filter presets.
     */
    public function management(Request $request): View
    {
        $this->authorize('view-management-dashboard');

        $preset = $request->input('preset', '30_days');
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        // Resolve date range from preset or custom input
        [$startDate, $endDate] = match ($preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7_days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()],
            'custom' => $this->parseCustomDateRange($fromInput, $toInput),
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
        };

        $metrics = $this->managementQuery->getMetrics($startDate, $endDate);

        return view('pages.dashboards.management', compact('metrics', 'preset', 'fromInput', 'toInput'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function parseCustomDateRange(?string $from, ?string $to): array
    {
        try {
            $startDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
            $endDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

            if ($startDate->gt($endDate)) {
                $temp = $startDate;
                $startDate = $endDate->copy()->startOfDay();
                $endDate = $temp->copy()->endOfDay();
            }

            return [$startDate, $endDate];
        } catch (\Throwable) {
            return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
        }
    }
}
