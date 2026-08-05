<?php

namespace App\Policies;

use App\Models\User;

class ClinicalActionPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('record-initial-actions') || $user->hasPermission('view-medical-visits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('record-initial-actions');
    }
}
