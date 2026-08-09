<?php

namespace App\Http\Controllers\Discharge;

use App\Actions\Discharge\EvaluateVisitDischargeReadinessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Discharge\AmendVisitDischargeRequest;
use App\Http\Requests\Discharge\FinalizeVisitDischargeRequest;
use App\Http\Requests\Discharge\StoreVisitDischargeRequest;
use App\Models\HealthcarePartner;
use App\Models\MedicalVisit;
use App\Models\VisitDischarge;
use App\Services\VisitDischargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitDischargeController extends Controller
{
    public function __construct(
        protected VisitDischargeService $dischargeService,
        protected EvaluateVisitDischargeReadinessAction $readinessEvaluator
    ) {}

    /**
     * Display a listing of discharged visits.
     *
     * Route: GET /discharges
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', VisitDischarge::class);

        $discharges = VisitDischarge::with(['medicalVisit.patient.person', 'preparedBy', 'finalizedBy'])
            ->latest('prepared_at')
            ->paginate(15);

        return view('pages.discharges.index', compact('discharges'));
    }

    /**
     * Show the discharge workspace for a medical visit.
     *
     * Route: GET /visits/{id}/discharge
     */
    public function workspace(string $visitId): View
    {
        $visit = MedicalVisit::with([
            'patient.person',
            'latestAssessment',
            'observationEpisodes',
            'medicationOrders',
            'discharge.versions',
            'discharge.followUpPlans',
            'discharge.activityRestrictions',
            'discharge.operationalHandoffs',
        ])->findOrFail($visitId);

        $this->authorize('viewAny', VisitDischarge::class);

        $readiness = $this->readinessEvaluator->execute($visit);
        $partners = HealthcarePartner::where('is_active', true)->get();

        return view('pages.discharges.workspace', compact('visit', 'readiness', 'partners'));
    }

    /**
     * Prepare or update a discharge draft for a visit.
     *
     * Route: POST /visits/{id}/discharge
     */
    public function store(StoreVisitDischargeRequest $request, string $visitId): RedirectResponse
    {
        $visit = MedicalVisit::findOrFail($visitId);
        $this->authorize('create', VisitDischarge::class);

        $discharge = $this->dischargeService->prepareDraft($visit, $request->validated(), $request->user());

        return redirect()->route('visits.discharge', $visit->id)
            ->with('status', 'Draf kepulangan klinis berhasil disimpan.');
    }

    /**
     * Display a specific discharge record.
     *
     * Route: GET /discharges/{id}
     */
    public function show(string $id): View
    {
        $discharge = VisitDischarge::with([
            'medicalVisit.patient.person',
            'preparedBy',
            'finalizedBy',
            'versions.generatedBy',
            'followUpPlans.healthcarePartner',
            'activityRestrictions.issuedBy',
            'operationalHandoffs.preparedBy',
        ])->findOrFail($id);

        $this->authorize('view', $discharge);

        return view('pages.discharges.show', compact('discharge'));
    }

    /**
     * Finalize a discharge draft and close the medical visit atomically.
     *
     * Route: POST /discharges/{id}/finalize
     */
    public function finalize(FinalizeVisitDischargeRequest $request, string $id): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($id);
        $this->authorize('finalize', $discharge);

        $finalized = $this->dischargeService->finalizeDischarge($discharge, $request->validated(), $request->user());

        return redirect()->route('discharges.show', $finalized->id)
            ->with('status', 'Kepulangan klinis berhasil difinalisasi dan kunjungan medis telah ditutup.');
    }

    /**
     * Amend a finalized discharge record.
     *
     * Route: POST /discharges/{id}/amend
     */
    public function amend(AmendVisitDischargeRequest $request, string $id): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($id);
        $this->authorize('amend', $discharge);

        $validated = $request->validated();
        $reason = $validated['amendment_reason'];
        unset($validated['amendment_reason']);

        $amended = $this->dischargeService->amendDischarge($discharge, $validated, $reason, $request->user());

        return redirect()->route('discharges.show', $amended->id)
            ->with('status', 'Kepulangan klinis berhasil diamandemen.');
    }
}
