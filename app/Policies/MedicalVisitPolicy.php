<?php

namespace App\Policies;

use App\Models\MedicalVisit;
use App\Models\User;

class MedicalVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-medical-visits') || $user->hasPermission('view-patients');
    }

    public function view(User $user, MedicalVisit $visit): bool
    {
        return $user->hasPermission('view-medical-visits') || $user->hasPermission('view-patients');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-medical-visits');
    }

    public function cancel(User $user, MedicalVisit $visit): bool
    {
        return $user->hasPermission('cancel-medical-visits');
    }

    public function overrideActive(User $user): bool
    {
        return $user->hasPermission('override-active-visit');
    }
}
