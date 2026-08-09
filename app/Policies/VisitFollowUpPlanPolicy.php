<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitFollowUpPlan;

class VisitFollowUpPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function view(User $user, VisitFollowUpPlan $plan): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-follow-up-plans');
    }

    public function update(User $user, VisitFollowUpPlan $plan): bool
    {
        return $user->hasPermission('manage-follow-up-plans');
    }

    public function complete(User $user, VisitFollowUpPlan $plan): bool
    {
        return $user->hasPermission('manage-follow-up-plans');
    }

    public function cancel(User $user, VisitFollowUpPlan $plan): bool
    {
        return $user->hasPermission('manage-follow-up-plans');
    }
}
