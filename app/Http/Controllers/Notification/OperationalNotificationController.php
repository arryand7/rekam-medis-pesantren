<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\AcknowledgeOperationalNotificationRequest;
use App\Models\OperationalNotification;
use App\Services\OperationalNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationalNotificationController extends Controller
{
    public function __construct(
        protected OperationalNotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', OperationalNotification::class);

        $recipientType = $request->query('recipient_type');
        $status = $request->query('status');

        $query = OperationalNotification::with(['person', 'medicalVisit.patient.person', 'preparedBy', 'acknowledgedBy'])
            ->latest('prepared_at');

        if (! empty($recipientType)) {
            $query->where('recipient_type', $recipientType);
        }
        if (! empty($status)) {
            $query->where('status', $status);
        }

        $notifications = $query->paginate(20)->withQueryString();

        return view('pages.notifications.operational', compact('notifications', 'recipientType', 'status'));
    }

    public function acknowledge(AcknowledgeOperationalNotificationRequest $request, string $id): RedirectResponse
    {
        $notification = OperationalNotification::findOrFail($id);
        $this->authorize('acknowledge', $notification);

        $this->notificationService->acknowledgeNotification(
            $notification,
            $request->input('acknowledgement_notes'),
            $request->user()
        );

        return redirect()->back()->with('success', 'Notifikasi operasional berhasil dikonfirmasi.');
    }
}
