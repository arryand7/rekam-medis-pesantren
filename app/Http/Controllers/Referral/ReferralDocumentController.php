<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralVersion;
use App\Services\ReferralDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Serves private referral documents through authorized download only.
 *
 * Security invariants enforced here:
 * - Policy check (authorize) before any file operation
 * - File served by controller, never via direct public URL
 * - Rate limiting applied via route middleware
 * - All downloads audited by ReferralDocumentService
 */
class ReferralDocumentController extends Controller
{
    public function __construct(private readonly ReferralDocumentService $documentService) {}

    /**
     * Download a referral version document.
     *
     * Route: GET /referrals/{referral}/versions/{version}/document
     */
    public function show(Request $request, string $referralId, string $versionId): mixed
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('downloadDocument', $referral);

        $version = ReferralVersion::where('referral_id', $referral->id)
            ->where('id', $versionId)
            ->firstOrFail();

        if (! $version->hasDocument()) {
            return redirect()->route('referrals.show', $referral->id)
                ->with('error', 'Dokumen belum tersedia. Hubungi petugas untuk membuat dokumen.');
        }

        return $this->documentService->serveDownload($version, $request->user());
    }

    /**
     * Generate/regenerate a private referral document.
     *
     * Route: POST /referrals/{referral}/versions/{version}/document/generate
     */
    public function generate(Request $request, string $referralId, string $versionId): RedirectResponse
    {
        $referral = Referral::findOrFail($referralId);
        $this->authorize('finalizeDocument', $referral);

        $version = ReferralVersion::where('referral_id', $referral->id)
            ->where('id', $versionId)
            ->firstOrFail();

        $this->documentService->generateDocument($version, $request->user());

        return redirect()->route('referrals.show', $referral->id)
            ->with('success', 'Dokumen rujukan berhasil dibuat.');
    }
}
