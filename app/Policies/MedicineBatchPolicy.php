<?php

namespace App\Policies;

use App\Models\User;

class MedicineBatchPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-pharmacy-inventory');
    }

    public function receive(User $user): bool
    {
        return $user->hasPermission('receive-medicine-stock');
    }

    public function adjust(User $user): bool
    {
        return $user->hasPermission('adjust-medicine-stock');
    }
}
