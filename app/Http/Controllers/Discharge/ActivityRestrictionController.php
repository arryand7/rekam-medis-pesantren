<?php

namespace App\Http\Controllers\Discharge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discharge\StoreActivityRestrictionRequest;
use App\Models\ActivityRestriction;
use App\Models\VisitDischarge;
use App\Services\VisitDischargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityRestrictionController extends Controller
{
    public function __construct(
        protected VisitDischargeService $dischargeService
    ) {}

    /**
     * Issue an activity restriction for a discharge.
     *
     * Route: POST /discharges/{id}/activity-restrictions
     */
    public function store(StoreActivityRestrictionRequest $request, string $dischargeId): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($dischargeId);
        $this->authorize('create', ActivityRestriction::class);

        $this->dischargeService->issueActivityRestriction($discharge, $request->validated(), $request->user());

        return back()->with('status', 'Rekomendasi pembatasan aktivitas/istirahat berhasil diterbitkan.');
    }

    /**
     * Cancel an activity restriction.
     *
     * Route: POST /activity-restrictions/{id}/cancel
     */
    public function cancel(Request $request, string $id): RedirectResponse
    {
        $restriction = ActivityRestriction::findOrFail($id);
        $this->authorize('cancel', $restriction);

        $this->dischargeService->cancelActivityRestriction($restriction, $request->user());

        return back()->with('status', 'Pembatasan aktivitas berhasil dibatalkan.');
    }
}
