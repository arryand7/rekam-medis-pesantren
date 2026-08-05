<?php

namespace App\Policies;

use App\Models\User;

class MedicinePolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-pharmacy-inventory') || $user->hasPermission('manage-medicine-master');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-medicine-master');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('manage-medicine-master');
    }
}
