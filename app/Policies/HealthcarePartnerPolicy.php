<?php

namespace App\Policies;

use App\Models\User;

class HealthcarePartnerPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-healthcare-partners');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('manage-healthcare-partners');
    }
}
