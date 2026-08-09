<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ClinicalDashboardService;
use App\Services\Dashboard\ManagementDashboardService;
use App\Services\Dashboard\OperationalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ClinicalDashboardService $clinicalService,
        protected ManagementDashboardService $managementService,
        protected OperationalDashboardService $operationalService
    ) {}

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
