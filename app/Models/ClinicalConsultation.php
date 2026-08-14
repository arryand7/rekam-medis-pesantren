<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClinicalConsultation extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'clinical_assessment_id',
        'observation_episode_id',
        'healthcare_partner_id',
        'recipient_contact_id',
        'purpose',
        'clinical_question',
        'urgency', // routine, urgent, emergency
        'status', // draft, ready, sent, acknowledged, responded, completed, cancelled, superseded_by_referral, entered_in_error
        'created_by_id',
        'finalized_at',
        'finalized_by_id',
        'sent_at',
        'sent_by_id',
        'acknowledged_at',
        'completed_at',
        'completed_by_id',
        'cancelled_at',
        'cancelled_by_id',
        'cancellation_reason',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function clinicalAssessment(): BelongsTo
    {
        return $this->belongsTo(ClinicalAssessment::class, 'clinical_assessment_id');
    }

    public function observationEpisode(): BelongsTo
    {
        return $this->belongsTo(ObservationEpisode::class, 'observation_episode_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function healthcarePartner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function recipientContact(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartnerContact::class, 'recipient_contact_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ClinicalConsultationVersion::class, 'clinical_consultation_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ClinicalConsultationVersion::class, 'clinical_consultation_id')->latestOfMany('version_number');
    }

    public function transmissions(): HasMany
    {
        return $this->hasMany(ClinicalConsultationTransmission::class, 'clinical_consultation_id');
    }

    public function externalAdvices(): HasMany
    {
        return $this->hasMany(ExternalClinicalAdvice::class, 'clinical_consultation_id');
    }

    public function latestAdvice(): HasOne
    {
        return $this->hasOne(ExternalClinicalAdvice::class, 'clinical_consultation_id')->where('status', 'finalized')->latestOfMany();
    }

    public function localDecisions(): HasMany
    {
        return $this->hasMany(ConsultationLocalDecision::class, 'clinical_consultation_id');
    }

    public function latestDecision(): HasOne
    {
        return $this->hasOne(ConsultationLocalDecision::class, 'clinical_consultation_id')->where('status', 'finalized')->latestOfMany();
    }
}
