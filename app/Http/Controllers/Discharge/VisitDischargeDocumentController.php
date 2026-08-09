<?php

namespace App\Http\Controllers\Discharge;

use App\Http\Controllers\Controller;
use App\Models\VisitDischarge;
use App\Models\VisitDischargeVersion;
use App\Services\VisitDischargeDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitDischargeDocumentController extends Controller
{
    public function __construct(
        protected VisitDischargeDocumentService $documentService
    ) {}

    /**
     * Download or stream the private discharge summary document.
     *
     * Route: GET /discharges/{dischargeId}/versions/{versionId}/document
     * Middleware: throttle:30,1
     */
    public function show(Request $request, string $dischargeId, string $versionId): StreamedResponse
    {
        $discharge = VisitDischarge::findOrFail($dischargeId);
        $this->authorize('downloadDocument', $discharge);

        $version = VisitDischargeVersion::where('visit_discharge_id', $discharge->id)
            ->findOrFail($versionId);

        return $this->documentService->streamDocument($version, $request->user());
    }

    /**
     * Generate the private discharge summary document for a version.
     *
     * Route: POST /discharges/{dischargeId}/versions/{versionId}/document/generate
     */
    public function generate(Request $request, string $dischargeId, string $versionId): RedirectResponse
    {
        $discharge = VisitDischarge::findOrFail($dischargeId);
        $this->authorize('generateDocument', $discharge);

        $version = VisitDischargeVersion::where('visit_discharge_id', $discharge->id)
            ->findOrFail($versionId);

        $this->documentService->generateDocument($version, $request->user());

        return back()->with('status', "Dokumen ringkasan kepulangan versi {$version->version_number} berhasil dibuat.");
    }
}
