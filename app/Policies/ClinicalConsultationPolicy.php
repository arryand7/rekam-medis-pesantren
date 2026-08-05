<?php

namespace App\Policies;

use App\Models\User;

class ClinicalConsultationPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('view-clinical-consultations') || $user->hasPermission('view-medical-visits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-clinical-consultations');
    }

    public function send(User $user): bool
    {
        return $user->hasPermission('send-clinical-consultations');
    }

    public function recordAdvice(User $user): bool
    {
        return $user->hasPermission('record-external-clinical-advice');
    }

    public function decide(User $user): bool
    {
        return $user->hasPermission('finalize-local-clinical-decisions');
    }
}
