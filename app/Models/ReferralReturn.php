<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReferralReturn extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'returned_at',
        'recorded_by_id',
        'return_transport_notes',
        'accompanied_by_notes',
        'external_outcome_summary',
        'external_diagnosis_text',
        'external_procedures_text',
        'external_medication_instructions',
        'restrictions_text',
        'follow_up_date',
        'follow_up_facility',
        'documents_received_notes',
        'status',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'follow_up_date' => 'date',
            'lock_version' => 'integer',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ReferralReturnReview::class, 'referral_return_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ReferralReturnReview::class, 'referral_return_id');
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(ReferralReturnReview::class, 'referral_return_id')->latestOfMany();
    }
}
