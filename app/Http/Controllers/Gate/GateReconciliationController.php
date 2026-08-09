<?php

namespace App\Http\Controllers\Gate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gate\ApproveIdentityMappingRequest;
use App\Models\GateIdentityMapping;
use App\Services\Gate\GateIdentityReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GateReconciliationController extends Controller
{
    public function __construct(
        protected GateIdentityReconciliationService $reconciliationService
    ) {}

    /**
     * Show reconciliation overview and identity mappings.
     */
    public function index(Request $request): View
    {
        Gate::authorize('view-gate-reconciliation');

        $status = $request->query('status', 'pending');
        $overview = $this->reconciliationService->getOverview();
        $mappings = $this->reconciliationService->getMappings($status, 15);

        return view('pages.gate.reconciliation', compact('overview', 'mappings', 'status'));
    }

    /**
     * Approve a candidate mapping.
     */
    public function approveMapping(ApproveIdentityMappingRequest $request, GateIdentityMapping $mapping): RedirectResponse
    {
        Gate::authorize('manage-identity-mappings');

        $this->reconciliationService->approveMapping(
            $mapping,
            $request->user(),
            $request->validated('notes')
        );

        return redirect()->back()->with('success', 'Pemetaan identitas Gate berhasil disetujui.');
    }

    /**
     * Reject a candidate mapping.
     */
    public function rejectMapping(Request $request, GateIdentityMapping $mapping): RedirectResponse
    {
        Gate::authorize('manage-identity-mappings');

        $this->reconciliationService->rejectMapping(
            $mapping,
            $request->user(),
            $request->input('notes')
        );

        return redirect()->back()->with('info', 'Pemetaan identitas Gate ditolak.');
    }
}
