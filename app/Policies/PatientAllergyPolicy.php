<?php

namespace App\Policies;

use App\Models\User;

class PatientAllergyPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-patient-health-profile') || $user->hasPermission('view-patients');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('manage-patient-allergies') || $user->hasPermission('update-patient-health-profile');
    }
}
