<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralRequest;
use App\Models\HealthcarePartner;
use App\Models\MedicalVisit;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Referral::class);

        $referrals = Referral::with(['medicalVisit.patient.person', 'partner'])
            ->latest()
            ->paginate(20);

        return view('pages.referrals.index', compact('referrals'));
    }

    public function create(string $visitId): View
    {
        $visit = MedicalVisit::findOrFail($visitId);
        $this->authorize('create', Referral::class);

        $partners = HealthcarePartner::where('is_active', true)->orderBy('name')->get();

        return view('pages.referrals.create', compact('visit', 'partners'));
    }

    public function store(StoreReferralRequest $request, string $visitId): RedirectResponse
    {
        $visit = MedicalVisit::findOrFail($visitId);
        // authorize() already handled by StoreReferralRequest::authorize()

        $referral = $this->referralService->createReferral(
            $visit,
            $request->validated(),
            $request->user()
        );

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Rujukan berhasil dibuat: '.$referral->referral_number);
    }

    public function show(string $id): View
    {
        $referral = Referral::with([
            'medicalVisit.patient.person',
            'partner',
            'latestVersion',
            'transports',
            'companions',
            'handovers',
            'statusEvents',
            'returnRecord',
            'returnRecord.returnReview',
        ])->findOrFail($id);

        $this->authorize('view', $referral);

        return view('pages.referrals.show', compact('referral'));
    }
}
