<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralCompanionRequest;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralCompanionController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(StoreReferralCompanionRequest $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('assignCompanion', $referral);

        $this->referralService->assignCompanion($referral, $request->validated(), $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Pendamping berhasil ditambahkan.');
    }
}
