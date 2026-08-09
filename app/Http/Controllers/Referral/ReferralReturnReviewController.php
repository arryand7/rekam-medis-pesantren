<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralReturnReviewRequest;
use App\Models\ReferralReturn;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralReturnReviewController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(StoreReferralReturnReviewRequest $request, string $referralReturnId): RedirectResponse
    {
        $referralReturn = ReferralReturn::with('referral')->findOrFail($referralReturnId);
        $this->authorize('recordReturnReview', $referralReturn->referral);

        $this->referralService->recordReturnReview($referralReturn, $request->validated(), $request->user());

        return redirect()->route('referrals.show', $referralReturn->referral_id)
            ->with('success', 'Tinjauan klinis kepulangan berhasil dicatat. Kunjungan tidak ditutup secara otomatis.');
    }
}
