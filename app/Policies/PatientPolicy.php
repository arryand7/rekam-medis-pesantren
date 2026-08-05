<?php

namespace App\Policies;

use App\Models\User;

class PatientPolicy
{
    /**
     * Rule: Admin users DO NOT automatically possess medical record access.
     * Permission 'view-patients' must be explicitly assigned.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-patients');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view-patients');
    }
}
