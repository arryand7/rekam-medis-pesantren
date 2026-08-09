<?php

use App\Models\User;
use App\Models\UserNotification;
use App\Services\UserNotificationService;

test('user in-app notification can be sent and marked as read', function () {
    $service = new UserNotificationService;
    $user = User::factory()->create();

    $notif = $service->notifyUser(
        user: $user,
        type: 'follow_up_due',
        title: 'Pengingat Kontrol Santri',
        body: 'Santri Ahmad memiliki jadwal kontrol hari ini di POSKESTREN.',
        payload: ['patient_id' => 'PAT-123']
    );

    expect($notif->user_id)->toBe($user->id);
    expect($notif->isRead())->toBeFalse();

    $service->markAsRead($notif);
    expect($notif->fresh()->isRead())->toBeTrue();
});

test('user can mark all unread notifications as read at once', function () {
    $service = new UserNotificationService;
    $user = User::factory()->create();

    $service->notifyUser($user, 'alert_1', 'Judul 1', 'Pesan 1');
    $service->notifyUser($user, 'alert_2', 'Judul 2', 'Pesan 2');

    expect(UserNotification::where('user_id', $user->id)->whereNull('read_at')->count())->toBe(2);

    $service->markAllAsRead($user);

    expect(UserNotification::where('user_id', $user->id)->whereNull('read_at')->count())->toBe(0);
});
