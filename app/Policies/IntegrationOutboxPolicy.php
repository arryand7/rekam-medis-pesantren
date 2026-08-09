<?php

namespace App\Policies;

use App\Models\IntegrationOutboxEvent;
use App\Models\User;

class IntegrationOutboxPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-integration-outbox');
    }

    public function view(User $user, IntegrationOutboxEvent $event): bool
    {
        return $user->hasPermission('view-integration-outbox');
    }

    public function retry(User $user, IntegrationOutboxEvent $event): bool
    {
        return $user->hasPermission('retry-integration-events');
    }
}
