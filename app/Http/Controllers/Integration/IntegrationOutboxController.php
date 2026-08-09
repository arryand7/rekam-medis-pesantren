<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\RetryOutboxEventRequest;
use App\Models\IntegrationOutboxEvent;
use App\Services\IntegrationOutboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationOutboxController extends Controller
{
    public function __construct(
        protected IntegrationOutboxService $outboxService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationOutboxEvent::class);

        $status = $request->query('status');
        $destination = $request->query('destination');

        $query = IntegrationOutboxEvent::with(['createdBy'])
            ->latest('created_at');

        if (! empty($status)) {
            $query->where('status', $status);
        }
        if (! empty($destination)) {
            $query->where('destination', $destination);
        }

        $events = $query->paginate(20)->withQueryString();

        return view('pages.integration.outbox', compact('events', 'status', 'destination'));
    }

    public function show(string $id): View
    {
        $event = IntegrationOutboxEvent::with(['deliveryAttempts', 'createdBy'])->findOrFail($id);
        $this->authorize('view', $event);

        return view('pages.integration.outbox-detail', compact('event'));
    }

    public function retry(RetryOutboxEventRequest $request, string $id): RedirectResponse
    {
        $event = IntegrationOutboxEvent::findOrFail($id);
        $this->authorize('retry', $event);

        $this->outboxService->retryEvent($event, $request->user());

        return redirect()->back()->with('success', 'Event outbox berhasil dijadwalkan ulang untuk pengiriman.');
    }
}
