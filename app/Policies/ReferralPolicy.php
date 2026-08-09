<?php

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;

class ReferralPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-referrals');
    }

    public function view(User $user, Referral $referral): bool
    {
        return $user->hasPermission('view-referrals');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-referrals');
    }

    public function approve(User $user, Referral $referral): bool
    {
        return $user->hasPermission('approve-referrals');
    }

    public function prepareDocument(User $user, Referral $referral): bool
    {
        return $user->hasPermission('prepare-referral-documents');
    }

    public function arrangeTransport(User $user, Referral $referral): bool
    {
        return $user->hasPermission('arrange-referral-transport');
    }

    public function assignCompanion(User $user, Referral $referral): bool
    {
        return $user->hasPermission('assign-referral-companions');
    }

    public function recordDeparture(User $user, Referral $referral): bool
    {
        return $user->hasPermission('record-referral-departure');
    }

    public function recordHandover(User $user, Referral $referral): bool
    {
        return $user->hasPermission('record-referral-handover');
    }

    public function recordDestinationStatus(User $user, Referral $referral): bool
    {
        return $user->hasPermission('record-destination-status');
    }

    public function cancel(User $user, Referral $referral): bool
    {
        return $user->hasPermission('cancel-referrals');
    }

    public function recordReturn(User $user, Referral $referral): bool
    {
        return $user->hasPermission('record-return-from-referral');
    }

    public function reviewReturn(User $user, Referral $referral): bool
    {
        return $user->hasPermission('review-return-from-referral');
    }

    /** Alias used by ReferralReturnReviewController. */
    public function recordReturnReview(User $user, Referral $referral): bool
    {
        return $user->hasPermission('review-return-from-referral');
    }

    /** Alias used by ReferralStatusController. */
    public function recordStatusEvent(User $user, Referral $referral): bool
    {
        return $user->hasPermission('record-destination-status');
    }

    /**
     * Download an existing private referral document.
     * Separate from prepareDocument so download can be audited independently.
     */
    public function downloadDocument(User $user, Referral $referral): bool
    {
        return $user->hasPermission('download-referral-document');
    }

    /**
     * Generate/finalize a referral document snapshot.
     * Only users with document preparation permission can trigger generation.
     */
    public function finalizeDocument(User $user, Referral $referral): bool
    {
        return $user->hasPermission('prepare-referral-documents');
    }
}
