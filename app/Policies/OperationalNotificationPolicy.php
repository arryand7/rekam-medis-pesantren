<?php

namespace App\Policies;

use App\Models\OperationalNotification;
use App\Models\User;

class OperationalNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-operational-notifications');
    }

    public function view(User $user, OperationalNotification $notification): bool
    {
        return $user->hasPermission('view-operational-notifications');
    }

    public function prepare(User $user): bool
    {
        return $user->hasPermission('prepare-operational-notifications');
    }

    public function acknowledge(User $user, OperationalNotification $notification): bool
    {
        return $user->hasPermission('acknowledge-operational-notifications');
    }
}
