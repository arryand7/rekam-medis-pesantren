<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalClinicalAdvice extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'external_clinical_advices';

    protected $fillable = [
        'clinical_consultation_id',
        'healthcare_partner_id',
        'recipient_contact_id',
        'clinician_name',
        'clinician_profession',
        'clinician_identifier',
        'department',
        'responded_at',
        'received_at',
        'channel',
        'advice_text',
        'limitations_text',
        'recommended_next_step',
        'verification_status', // unverified, partially_verified, verified, refuted
        'verified_at',
        'verified_by_id',
        'recorded_by_id',
        'status', // draft, finalized, amended, entered_in_error
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function recipientContact(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartnerContact::class, 'recipient_contact_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
