<?php

namespace App\Policies;

use App\Models\User;

class StockLocationPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-pharmacy-inventory') || $user->hasPermission('manage-stock-locations');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('manage-stock-locations');
    }
}
