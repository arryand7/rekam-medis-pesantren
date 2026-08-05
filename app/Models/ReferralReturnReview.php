<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReturnReview extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_return_id',
        'local_reviewer_id',
        'review_summary',
        'decision_type', // continue_poskestren_care, continue_observation, follow_up_external, rest_recommended, return_to_activity_recommended, new_referral_recommended, emergency_referral_required, other
        'medication_reconciliation_note',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
        ];
    }

    public function referralReturn(): BelongsTo
    {
        return $this->belongsTo(ReferralReturn::class, 'referral_return_id');
    }

    public function localReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'local_reviewer_id');
    }
}
