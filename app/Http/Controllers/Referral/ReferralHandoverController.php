<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralHandoverController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function store(Request $request, string $referralId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('recordHandover', $referral);

        $data = $request->only(['notes', 'idempotency_key']);

        $this->referralService->recordHandover($referral, $data, $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Serah terima klinis berhasil dicatat.');
    }
}
