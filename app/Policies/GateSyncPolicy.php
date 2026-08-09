<?php

namespace App\Policies;

use App\Models\User;

class GateSyncPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-gate-sync');
    }

    public function apply(User $user): bool
    {
        return $user->hasPermission('execute-gate-sync-apply');
    }
}
