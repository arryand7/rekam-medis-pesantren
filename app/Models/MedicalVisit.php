<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $visit_number
 * @property string $patient_id
 * @property string $status
 * @property Carbon|null $arrived_at
 * @property string $chief_complaint
 * @property string|null $reporting_type
 * @property string|null $reporting_name
 * @property string|null $origin_location
 * @property string|null $receiving_officer_id
 * @property string|null $assigned_officer_id
 * @property string|null $cancellation_reason
 * @property string $created_by_id
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MedicalVisit extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'visit_number',
        'patient_id',
        'status', // registered, waiting_assessment, under_assessment, assessment_completed, under_observation, observation_completed, external_consultation_pending, external_consultation_completed, cancelled
        'arrived_at',
        'chief_complaint',
        'reporting_type',
        'reporting_name',
        'origin_location',
        'receiving_officer_id',
        'assigned_officer_id',
        'cancellation_reason',
        'created_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'arrived_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function receivingOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiving_officer_id');
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class, 'medical_visit_id');
    }

    public function latestVitalSign(): HasOne
    {
        return $this->hasOne(VitalSign::class, 'medical_visit_id')->where('status', 'finalized')->latestOfMany();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(ClinicalAssessment::class, 'medical_visit_id');
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(ClinicalAssessment::class, 'medical_visit_id')->whereIn('status', ['finalized', 'amended'])->latestOfMany();
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ClinicalAction::class, 'medical_visit_id');
    }

    public function observationEpisodes(): HasMany
    {
        return $this->hasMany(ObservationEpisode::class, 'medical_visit_id');
    }

    public function activeObservationEpisode(): HasOne
    {
        return $this->hasOne(ObservationEpisode::class, 'medical_visit_id')->whereIn('status', ['planned', 'active']);
    }

    public function medicationOrders(): HasMany
    {
        return $this->hasMany(MedicationOrder::class, 'medical_visit_id');
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class, 'medical_visit_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(ClinicalConsultation::class, 'medical_visit_id');
    }

    public function discharge(): HasOne
    {
        return $this->hasOne(VisitDischarge::class, 'medical_visit_id');
    }

    public function discharges(): HasMany
    {
        return $this->hasMany(VisitDischarge::class, 'medical_visit_id');
    }

    public function operationalHandoffs(): HasMany
    {
        return $this->hasMany(ClinicalOperationalHandoff::class, 'medical_visit_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            'registered',
            'waiting_assessment',
            'under_assessment',
            'under_observation',
            'external_consultation_pending',
            'referral_prepared',
            'discharge_prepared',
        ], true);
    }

    /**
     * Generate server-side authoritative visit number.
     */
    public static function generateVisitNumber(): string
    {
        $prefix = 'VIS-'.date('Ymd');
        $random = strtoupper(Str::random(5));

        return "{$prefix}-{$random}";
    }
}
