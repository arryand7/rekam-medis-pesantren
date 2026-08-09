<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-health-reports');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('export-health-reports');
    }
}
