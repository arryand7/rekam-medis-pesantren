<?php

namespace App\Policies;

use App\Models\User;

class GateMappingPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-gate-reconciliation');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('manage-identity-mappings');
    }
}
