<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\QueryReportRequest;
use App\Services\AuditLogService;
use App\Services\Reporting\HealthReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthReportController extends Controller
{
    public function __construct(
        protected HealthReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('view-health-reports');

        return view('pages.reports.index');
    }

    public function show(QueryReportRequest $request): View
    {
        $this->authorize('view-health-reports');

        $reportType = $request->input('report_type');
        $filters = $request->validated();
        $perPage = (int) ($request->input('per_page', 20));

        $data = match ($reportType) {
            'observation_census' => $this->reportService->getObservationCensus($filters, $perPage),
            'referral_census' => $this->reportService->getReferralCensus($filters, $perPage),
            'discharge_followup' => $this->reportService->getDischargeReport($filters, $perPage),
            'pharmacy_stock' => $this->reportService->getPharmacyStockReport($filters, $perPage),
            'integration_delivery' => $this->reportService->getIntegrationDeliveryReport($filters, $perPage),
            default => $this->reportService->getVisitCensus($filters, $perPage),
        };

        $actor = $request->user();
        $actorName = $actor !== null ? $actor->name : 'Sistem';

        // Audit report query
        AuditLogService::log(
            action: 'health_report.viewed',
            subjectType: 'HealthReport',
            subjectId: null,
            before: null,
            after: ['report_type' => $reportType, 'filters' => $filters],
            reason: 'Laporan kesehatan '.$reportType.' dilihat oleh '.$actorName
        );

        return view('pages.reports.show', compact('reportType', 'data', 'filters'));
    }
}
