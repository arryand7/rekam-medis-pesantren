<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

/**
 * Service managing in-app internal notifications for health workers, nurses, and administrators.
 */
class UserNotificationService
{
    /**
     * Send an in-app notification to a user.
     *
     * @param  array<string, mixed>|null  $payload
     */
    public function notifyUser(
        User $user,
        string $type,
        string $title,
        string $body,
        ?array $payload = null,
        ?string $sourceType = null,
        ?string $sourceId = null
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $user->id,
            'notification_type' => $type,
            'title' => trim($title),
            'body' => trim($body),
            'payload_snapshot' => $payload,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(UserNotification $notification): UserNotification
    {
        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return $notification;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Acknowledge an in-app user notification.
     */
    public function acknowledgeNotification(UserNotification $notification): UserNotification
    {
        $notification->update([
            'read_at' => $notification->read_at ?? now(),
            'acknowledged_at' => now(),
        ]);

        return $notification;
    }
}
