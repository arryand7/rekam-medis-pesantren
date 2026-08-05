<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalConsultationTransmission extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'clinical_consultation_id',
        'clinical_consultation_version_id',
        'healthcare_partner_id',
        'recipient_contact_id',
        'channel',
        'status', // queued, sending, sent, acknowledged, failed, cancelled
        'idempotency_key',
        'external_reference',
        'attempted_at',
        'sent_at',
        'acknowledged_at',
        'failed_at',
        'failure_code',
        'failure_message_sanitized',
        'initiated_by_id',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultationVersion::class, 'clinical_consultation_version_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function recipientContact(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartnerContact::class, 'recipient_contact_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }
}
