<?php

namespace App\Http\Controllers\Integration;

use App\Contracts\Integration\AttendanceIntegrationContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\ResolveIdentityConflictRequest;
use App\Models\IntegrationIdentityConflict;
use App\Services\IntegrationOutboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceIntegrationController extends Controller
{
    public function __construct(
        protected AttendanceIntegrationContract $attendanceContract,
        protected IntegrationOutboxService $outboxService
    ) {}

    public function status(Request $request): View
    {
        $this->authorize('viewAny', IntegrationIdentityConflict::class);

        $probe = $this->attendanceContract->probeHealth();
        $config = config('integration.attendance');

        return view('pages.integration.attendance', compact('probe', 'config'));
    }

    public function conflicts(Request $request): View
    {
        $this->authorize('viewAny', IntegrationIdentityConflict::class);

        $status = $request->query('status', 'open');
        $conflicts = IntegrationIdentityConflict::with(['person', 'resolvedBy'])
            ->where('status', $status)
            ->latest('created_at')
            ->paginate(20);

        return view('pages.integration.conflicts', compact('conflicts', 'status'));
    }

    public function resolveConflict(ResolveIdentityConflictRequest $request, string $id): RedirectResponse
    {
        $conflict = IntegrationIdentityConflict::findOrFail($id);
        $this->authorize('resolve', $conflict);

        $this->outboxService->resolveConflict($conflict, $request->input('resolution_notes'), $request->user());

        return redirect()->back()->with('success', 'Konflik identitas integrasi berhasil diselesaikan.');
    }
}
