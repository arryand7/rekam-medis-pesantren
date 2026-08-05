<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-audit-log');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view-audit-log');
    }

    public function create(User $user): bool
    {
        return false; // Audit logs are created automatically via events/service, not via manual API
    }

    public function update(User $user): bool
    {
        return false; // Append-only immutable log
    }

    public function delete(User $user): bool
    {
        return false; // Append-only immutable log
    }
}
