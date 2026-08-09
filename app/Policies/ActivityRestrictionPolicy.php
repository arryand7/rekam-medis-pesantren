<?php

namespace App\Policies;

use App\Models\ActivityRestriction;
use App\Models\User;

class ActivityRestrictionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function view(User $user, ActivityRestriction $restriction): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-activity-restrictions');
    }

    public function cancel(User $user, ActivityRestriction $restriction): bool
    {
        return $user->hasPermission('manage-activity-restrictions');
    }
}
