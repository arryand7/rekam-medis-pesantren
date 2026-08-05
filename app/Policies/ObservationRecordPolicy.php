<?php

namespace App\Policies;

use App\Models\User;

class ObservationRecordPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-observations') || $user->hasPermission('view-medical-visits');
    }

    public function record(User $user): bool
    {
        return $user->hasPermission('record-observation-monitoring');
    }
}
