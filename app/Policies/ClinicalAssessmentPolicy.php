<?php

namespace App\Policies;

use App\Models\ClinicalAssessment;
use App\Models\User;

class ClinicalAssessmentPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('create-clinical-assessments') || $user->hasPermission('view-medical-visits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-clinical-assessments');
    }

    public function update(User $user, ClinicalAssessment $assessment): bool
    {
        if ($assessment->status === 'finalized' || $assessment->status === 'amended') {
            return false; // Finalized assessments cannot be edited directly!
        }

        return $user->hasPermission('create-clinical-assessments');
    }

    public function finalize(User $user): bool
    {
        return $user->hasPermission('finalize-clinical-assessments') || $user->hasPermission('create-clinical-assessments');
    }

    public function amend(User $user): bool
    {
        return $user->hasPermission('amend-clinical-assessments');
    }
}
