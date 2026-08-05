<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationLocalDecision extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'clinical_consultation_id',
        'external_clinical_advice_id',
        'decision_type', // continue_current_care, continue_observation, return_to_activity_recommended, rest_recommended, follow_up_required, referral_recommended, emergency_referral_required, other
        'rationale',
        'decided_by_id',
        'decided_at',
        'status', // draft, finalized, amended, entered_in_error
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function externalAdvice(): BelongsTo
    {
        return $this->belongsTo(ExternalClinicalAdvice::class, 'external_clinical_advice_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_id');
    }
}
