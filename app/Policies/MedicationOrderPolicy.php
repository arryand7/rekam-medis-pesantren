<?php

namespace App\Policies;

use App\Models\User;

class MedicationOrderPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-medication-orders') || $user->hasPermission('view-medical-visits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-medication-orders');
    }

    public function activate(User $user): bool
    {
        return $user->hasPermission('activate-medication-orders');
    }

    public function discontinue(User $user): bool
    {
        return $user->hasPermission('discontinue-medication-orders');
    }
}
