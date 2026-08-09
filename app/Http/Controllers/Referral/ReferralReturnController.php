<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralReturnRequest;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralReturnController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(StoreReferralReturnRequest $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('recordReturn', $referral);

        $this->referralService->recordReturn($referral, $request->validated(), $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Kepulangan dari rujukan berhasil dicatat.');
    }
}
