<?php

namespace App\Http\Controllers\Gate;

use App\Contracts\GateClientContract;
use App\Contracts\GateOidcClientContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gate\ApplyGateSyncRequest;
use App\Models\GateSyncRun;
use App\Services\Gate\GateSyncApplyService;
use App\Services\Gate\GateSyncDryRunService;
use App\Services\SsoConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GateSyncController extends Controller
{
    public function __construct(
        protected GateSyncDryRunService $dryRunService,
        protected GateSyncApplyService $applyService,
        protected GateClientContract $gateClient,
        protected GateOidcClientContract $oidcClient,
        protected SsoConfigurationService $ssoConfiguration
    ) {}

    /**
     * Show Gate synchronization dashboard.
     */
    public function index(): View
    {
        Gate::authorize('view-gate-sync');

        $health = $this->oidcClient->probeHealth();
        $sso = $this->ssoConfiguration->forForm();
        $recentRuns = GateSyncRun::latest('started_at')->paginate(10);

        return view('pages.gate.sync', compact('health', 'sso', 'recentRuns'));
    }

    /**
     * Execute a non-mutating dry-run preview.
     */
    public function dryRun(Request $request): View
    {
        Gate::authorize('view-gate-sync');

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 50);

        $preview = $this->dryRunService->execute($page, $perPage);

        return view('pages.gate.dry-run-preview', compact('preview', 'page', 'perPage'));
    }

    /**
     * Execute transactional Apply Sync.
     */
    public function apply(ApplyGateSyncRequest $request): RedirectResponse
    {
        Gate::authorize('execute-gate-sync-apply');

        $page = (int) ($request->validated('page') ?? 1);
        $perPage = (int) ($request->validated('per_page') ?? 50);

        $result = $this->applyService->executeApply($page, $perPage, $request->user());

        $summary = $result['summary'];
        $message = "Sinkronisasi selesai: {$summary['applied_new']} baru, {$summary['applied_changed']} diperbarui, {$summary['unchanged']} tidak berubah, {$summary['conflicts']} konflik.";

        return redirect()->route('gate.sync.show', $result['run_id'])
            ->with('success', $message);
    }

    /**
     * Show detail of a sync run.
     */
    public function showRun(GateSyncRun $run): View
    {
        Gate::authorize('view-gate-sync');

        return view('pages.gate.run-detail', compact('run'));
    }
}
