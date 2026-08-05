<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'clinical_assessment_id',
        'observation_episode_id',
        'clinical_consultation_id',
        'consultation_local_decision_id',
        'healthcare_partner_id',
        'recipient_contact_id',
        'referral_number',
        'urgency', // routine, urgent, emergency
        'reason',
        'clinical_summary',
        'requested_service_or_department',
        'status', // draft, prepared, approved, ready_to_depart, departed, arrived, accepted, under_external_care, return_planned, returned, completed, cancelled, declined_by_destination, superseded, entered_in_error
        'initiated_by_id',
        'initiated_at',
        'approved_by_id',
        'approved_at',
        'ready_at',
        'departed_at',
        'arrived_at_destination',
        'accepted_at_destination',
        'returned_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by_id',
        'cancellation_reason',
        'supersedes_referral_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'initiated_at' => 'datetime',
            'approved_at' => 'datetime',
            'ready_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at_destination' => 'datetime',
            'accepted_at_destination' => 'datetime',
            'returned_at' => 'datetime',
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

    public function versions(): HasMany
    {
        return $this->hasMany(ReferralVersion::class, 'referral_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ReferralVersion::class, 'referral_id')->latestOfMany('version_number');
    }

    public function transports(): HasMany
    {
        return $this->hasMany(ReferralTransport::class, 'referral_id');
    }

    public function latestTransport(): HasOne
    {
        return $this->hasOne(ReferralTransport::class, 'referral_id')->latestOfMany();
    }

    public function companions(): HasMany
    {
        return $this->hasMany(ReferralCompanion::class, 'referral_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ReferralHandover::class, 'referral_id');
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(ReferralReturn::class, 'referral_id');
    }

    /**
     * Generate concurrency-safe unique referral number.
     */
    public static function generateReferralNumber(): string
    {
        $prefix = 'REF-'.date('Ymd');
        $random = strtoupper(Str::random(5));

        return "{$prefix}-{$random}";
    }
}
