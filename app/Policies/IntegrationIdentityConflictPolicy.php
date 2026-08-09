<?php

namespace App\Policies;

use App\Models\IntegrationIdentityConflict;
use App\Models\User;

class IntegrationIdentityConflictPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-integration-outbox');
    }

    public function resolve(User $user, IntegrationIdentityConflict $conflict): bool
    {
        return $user->hasPermission('resolve-integration-conflicts');
    }
}
