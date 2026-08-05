<?php

namespace App\Policies;

use App\Models\User;

class PatientHealthProfilePolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-patient-health-profile') || $user->hasPermission('view-patients');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update-patient-health-profile');
    }
}
