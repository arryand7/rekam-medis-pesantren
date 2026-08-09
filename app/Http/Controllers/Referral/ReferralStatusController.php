<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralStatusEventRequest;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralStatusController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(StoreReferralStatusEventRequest $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('recordStatusEvent', $referral);

        $this->referralService->recordStatusEvent($referral, $request->validated(), $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Status destinasi berhasil dicatat.');
    }
}
