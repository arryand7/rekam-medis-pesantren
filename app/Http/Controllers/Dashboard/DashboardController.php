<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ClinicalDashboardService;
use App\Services\Dashboard\ManagementDashboardService;
use App\Services\Dashboard\OperationalDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ClinicalDashboardService $clinicalService,
        protected ManagementDashboardService $managementService,
        protected OperationalDashboardService $operationalService
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

    public function clinical(Request $request): View
    {
        $this->authorize('view-clinical-dashboard');

        $metrics = $this->clinicalService->getMetrics();

        return view('pages.dashboards.clinical', compact('metrics'));
    }

    public function management(Request $request): View
    {
        $this->authorize('view-management-dashboard');

        $metrics = $this->managementService->getAggregatedMetrics();

        return view('pages.dashboards.management', compact('metrics'));
    }

    public function operational(Request $request): View
    {
        $this->authorize('view-operational-dashboard');

        $overview = $this->operationalService->getOperationalOverview();

        return view('pages.dashboards.operational', compact('overview'));
    }
}
