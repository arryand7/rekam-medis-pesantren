<?php

namespace App\Policies;

use App\Models\User;

class ObservationEpisodePolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-observations') || $user->hasPermission('view-medical-visits');
    }

    public function start(User $user): bool
    {
        return $user->hasPermission('start-observations');
    }

    public function complete(User $user): bool
    {
        return $user->hasPermission('complete-observations');
    }

    public function cancel(User $user): bool
    {
        return $user->hasPermission('cancel-observations');
    }
}
