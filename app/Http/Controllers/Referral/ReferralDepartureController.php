<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralDepartureController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(Request $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('recordDeparture', $referral);

        $this->referralService->recordDeparture($referral, [], $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Keberangkatan pasien berhasil dicatat.');
    }
}
