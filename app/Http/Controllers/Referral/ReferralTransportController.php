<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralTransportRequest;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralTransportController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(StoreReferralTransportRequest $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('arrangeTransport', $referral);

        $this->referralService->arrangeTransport($referral, $request->validated(), $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Transportasi berhasil diatur.');
    }
}
