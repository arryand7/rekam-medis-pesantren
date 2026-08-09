<?php

namespace App\Policies;

use App\Models\ClinicalOperationalHandoff;
use App\Models\User;

class ClinicalOperationalHandoffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function view(User $user, ClinicalOperationalHandoff $handoff): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('prepare-operational-handoffs');
    }

    public function acknowledge(User $user, ClinicalOperationalHandoff $handoff): bool
    {
        return $user->hasPermission('acknowledge-operational-handoffs');
    }
}
