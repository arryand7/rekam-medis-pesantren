<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitDischarge;

class VisitDischargePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function view(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('view-visit-discharges');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('prepare-visit-discharges');
    }

    public function prepare(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('prepare-visit-discharges');
    }

    public function finalize(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('finalize-visit-discharges');
    }

    public function amend(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('amend-visit-discharges');
    }

    public function downloadDocument(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('download-discharge-summaries');
    }

    public function generateDocument(User $user, VisitDischarge $discharge): bool
    {
        return $user->hasPermission('finalize-visit-discharges');
    }
}
