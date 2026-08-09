<?php

namespace App\Http\Controllers\Discharge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discharge\StoreFollowUpPlanRequest;
use App\Models\VisitDischarge;
use App\Models\VisitFollowUpPlan;
use App\Services\VisitDischargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitFollowUpPlanController extends Controller
{
    public function __construct(
        protected VisitDischargeService $dischargeService
    ) {}

    /**
     * Display a listing of follow-up plans.
     *
     * Route: GET /follow-up-plans
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', VisitFollowUpPlan::class);

        $plans = VisitFollowUpPlan::with(['visitDischarge.medicalVisit.patient.person', 'healthcarePartner', 'createdBy'])
            ->latest('created_at')
            ->paginate(15);

        return view('pages.discharges.follow-ups', compact('plans'));
    }

    /**
     * Store a follow-up plan for a discharge.
     *
     * Route: POST /discharges/{id}/follow-up-plans
     */
    public function store(StoreFollowUpPlanRequest $request, string $dischargeId): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($dischargeId);
        $this->authorize('create', VisitFollowUpPlan::class);

        $this->dischargeService->addFollowUpPlan($discharge, $request->validated(), $request->user());

        return back()->with('status', 'Rencana tindak lanjut (follow-up) berhasil ditambahkan.');
    }

    /**
     * Complete a follow-up plan manually.
     *
     * Route: POST /follow-up-plans/{id}/complete
     */
    public function complete(Request $request, string $id): RedirectResponse
    {
        $plan = VisitFollowUpPlan::findOrFail($id);
        $this->authorize('complete', $plan);

        $this->dischargeService->completeFollowUpPlan($plan, $request->input('notes'), $request->user());

        return back()->with('status', 'Rencana tindak lanjut berhasil diselesaikan.');
    }

    /**
     * Cancel a follow-up plan.
     *
     * Route: POST /follow-up-plans/{id}/cancel
     */
    public function cancel(Request $request, string $id): RedirectResponse
    {
        $request->validate(['cancellation_reason' => ['required', 'string', 'min:3', 'max:1000']]);

        $plan = VisitFollowUpPlan::findOrFail($id);
        $this->authorize('cancel', $plan);

        $this->dischargeService->cancelFollowUpPlan($plan, $request->input('cancellation_reason'), $request->user());

        return back()->with('status', 'Rencana tindak lanjut berhasil dibatalkan.');
    }
}
