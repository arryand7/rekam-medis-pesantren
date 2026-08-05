<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VitalSign;

class VitalSignPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('record-vital-signs') || $user->hasPermission('view-medical-visits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('record-vital-signs');
    }

    public function update(User $user, VitalSign $vital): bool
    {
        if ($vital->status === 'finalized') {
            return false; // Finalized records cannot be edited directly!
        }

        return $user->hasPermission('record-vital-signs');
    }

    public function finalize(User $user): bool
    {
        return $user->hasPermission('finalize-vital-signs') || $user->hasPermission('record-vital-signs');
    }
}
