<?php

namespace App\Http\Controllers\Discharge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discharge\AcknowledgeOperationalHandoffRequest;
use App\Http\Requests\Discharge\StoreOperationalHandoffRequest;
use App\Models\ClinicalOperationalHandoff;
use App\Models\VisitDischarge;
use App\Services\VisitDischargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalOperationalHandoffController extends Controller
{
    public function __construct(
        protected VisitDischargeService $dischargeService
    ) {}

    /**
     * Display a listing of operational handoffs.
     *
     * Route: GET /operational-handoffs
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClinicalOperationalHandoff::class);

        $handoffs = ClinicalOperationalHandoff::with(['medicalVisit.patient.person', 'preparedBy', 'acknowledgedBy'])
            ->latest('prepared_at')
            ->paginate(15);

        return view('pages.discharges.handoffs', compact('handoffs'));
    }

    /**
     * Store an operational handoff for a discharge.
     *
     * Route: POST /discharges/{id}/operational-handoffs
     */
    public function store(StoreOperationalHandoffRequest $request, string $dischargeId): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($dischargeId);
        $this->authorize('create', ClinicalOperationalHandoff::class);

        $this->dischargeService->createOperationalHandoff($discharge, $request->validated(), $request->user());

        return back()->with('status', 'Serah terima operasional internal berhasil disiapkan.');
    }

    /**
     * Acknowledge receipt of an operational handoff.
     *
     * Route: POST /operational-handoffs/{id}/acknowledge
     */
    public function acknowledge(AcknowledgeOperationalHandoffRequest $request, string $id): RedirectResponse
    {
        $handoff = ClinicalOperationalHandoff::findOrFail($id);
        $this->authorize('acknowledge', $handoff);

        $this->dischargeService->acknowledgeOperationalHandoff($handoff, $request->input('acknowledgement_notes'), $request->user());

        return back()->with('status', 'Penerimaan handoff operasional berhasil dikonfirmasi.');
    }
}
