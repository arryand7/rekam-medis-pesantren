<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Clinical discharge and visit closure record.
 *
 * @property string $id
 * @property string $medical_visit_id
 * @property string $discharge_type
 * @property string $discharge_destination
 * @property string $clinical_summary
 * @property string $final_condition
 * @property string $activity_recommendation
 * @property string|null $rest_recommendation
 * @property string|null $restriction_notes
 * @property bool $follow_up_required
 * @property string|null $follow_up_summary
 * @property Carbon|null $follow_up_date
 * @property string|null $follow_up_partner_id
 * @property string $prepared_by_id
 * @property Carbon $prepared_at
 * @property string|null $finalized_by_id
 * @property Carbon|null $finalized_at
 * @property string $status
 * @property string|null $parent_discharge_id
 * @property string|null $amendment_reason
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class VisitDischarge extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'discharge_type',
        'discharge_destination',
        'clinical_summary',
        'final_condition',
        'activity_recommendation',
        'rest_recommendation',
        'restriction_notes',
        'follow_up_required',
        'follow_up_summary',
        'follow_up_date',
        'follow_up_partner_id',
        'prepared_by_id',
        'prepared_at',
        'finalized_by_id',
        'finalized_at',
        'status',
        'parent_discharge_id',
        'amendment_reason',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'datetime',
            'prepared_at' => 'datetime',
            'finalized_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by_id');
    }

    public function followUpPartner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'follow_up_partner_id');
    }

    public function parentDischarge(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_discharge_id');
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_discharge_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(VisitDischargeVersion::class, 'visit_discharge_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(VisitDischargeVersion::class, 'visit_discharge_id')
            ->latestOfMany('version_number');
    }

    public function followUpPlans(): HasMany
    {
        return $this->hasMany(VisitFollowUpPlan::class, 'visit_discharge_id');
    }

    public function activityRestrictions(): HasMany
    {
        return $this->hasMany(ActivityRestriction::class, 'visit_discharge_id');
    }

    public function operationalHandoffs(): HasMany
    {
        return $this->hasMany(ClinicalOperationalHandoff::class, 'visit_discharge_id');
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized' || $this->status === 'amended';
    }
}
