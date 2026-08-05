<?php

namespace App\Policies;

use App\Models\User;

class MedicationAdministrationPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-medication-administrations') || $user->hasPermission('view-medical-visits');
    }

    public function schedule(User $user): bool
    {
        return $user->hasPermission('schedule-medication-administrations');
    }

    public function administer(User $user): bool
    {
        return $user->hasPermission('administer-medications');
    }

    public function correct(User $user): bool
    {
        return $user->hasPermission('correct-medication-administrations');
    }
}
