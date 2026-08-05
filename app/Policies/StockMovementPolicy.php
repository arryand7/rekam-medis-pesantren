<?php

namespace App\Policies;

use App\Models\User;

class StockMovementPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-stock-movements') || $user->hasPermission('view-pharmacy-inventory');
    }

    public function reverse(User $user): bool
    {
        return $user->hasPermission('reverse-stock-movements');
    }
}
