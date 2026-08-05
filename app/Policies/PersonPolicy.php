<?php

namespace App\Policies;

use App\Models\User;

class PersonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-people');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view-people');
    }
}
