<?php

namespace App\Policies;

use App\Models\User;

class GateSyncPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-gate-sync') || $user->hasPermission('manage-gate-sync');
    }

    public function dryRun(User $user): bool
    {
        return $user->hasPermission('manage-gate-sync');
    }

    public function resolveConflict(User $user): bool
    {
        return $user->hasPermission('resolve-identity-conflicts') || $user->hasPermission('manage-gate-sync');
    }
}
