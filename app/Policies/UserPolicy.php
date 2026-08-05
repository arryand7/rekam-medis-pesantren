<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-users');
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->id === $targetUser->id || $user->hasPermission('manage-users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-users');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->hasPermission('manage-users');
    }

    public function delete(User $user, User $targetUser): bool
    {
        // Users cannot delete themselves or escalate privileges
        if ($user->id === $targetUser->id) {
            return false;
        }

        return $user->hasPermission('manage-users');
    }
}
