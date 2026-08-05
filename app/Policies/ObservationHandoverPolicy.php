<?php

namespace App\Policies;

use App\Models\User;

class ObservationHandoverPolicy
{
    public function prepare(User $user): bool
    {
        return $user->hasPermission('prepare-observation-handover') || $user->hasPermission('record-observation-monitoring');
    }

    public function acknowledge(User $user): bool
    {
        return $user->hasPermission('acknowledge-observation-handover') || $user->hasPermission('record-observation-monitoring');
    }
}
