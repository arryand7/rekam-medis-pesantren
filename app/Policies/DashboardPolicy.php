<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function viewClinical(User $user): bool
    {
        return $user->hasPermission('view-clinical-dashboard');
    }

    public function viewManagement(User $user): bool
    {
        return $user->hasPermission('view-management-dashboard');
    }

    public function viewOperational(User $user): bool
    {
        return $user->hasPermission('view-operational-dashboard');
    }

    public function viewPharmacy(User $user): bool
    {
        return $user->hasPermission('view-pharmacy-dashboard') || $user->hasPermission('view-pharmacy-inventory');
    }
}
