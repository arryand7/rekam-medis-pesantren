<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function __construct(
        protected UserNotificationService $userNotificationService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = UserNotification::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        return view('pages.notifications.user-inbox', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = UserNotification::where('user_id', $request->user()->id)->findOrFail($id);
        $this->userNotificationService->markAsRead($notification);

        return redirect()->back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->userNotificationService->markAllAsRead($request->user());

        return redirect()->back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
