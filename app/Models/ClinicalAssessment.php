<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalAssessment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'author_id',
        'history_current_illness',
        'relevant_history',
        'examination_findings',
        'assessment_summary',
        'working_diagnosis',
        'status',
        'disposition_recommendation',
        'parent_assessment_id',
        'amendment_reason',
        'finalized_at',
        'finalized_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by_id');
    }

    public function parentAssessment(): BelongsTo
    {
        return $this->belongsTo(ClinicalAssessment::class, 'parent_assessment_id');
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(ClinicalAssessment::class, 'parent_assessment_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ClinicalAction::class, 'clinical_assessment_id');
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, ['finalized', 'amended'], true);
    }
}
