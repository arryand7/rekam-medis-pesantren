<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationOrder extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'clinical_assessment_id',
        'medicine_id',
        'dose_value',
        'dose_unit',
        'route',
        'frequency_text',
        'instructions',
        'start_at',
        'end_at',
        'quantity_per_administration',
        'ordered_by_id',
        'ordered_at',
        'status', // draft, active, completed, discontinued, cancelled, entered_in_error
        'reason_or_indication',
        'discontinued_at',
        'discontinued_by_id',
        'discontinuation_reason',
        'parent_order_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'ordered_at' => 'datetime',
            'discontinued_at' => 'datetime',
            'quantity_per_administration' => 'integer',
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

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by_id');
    }

    public function discontinuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discontinued_by_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(MedicationOrder::class, 'parent_order_id');
    }

    public function administrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class, 'medication_order_id');
    }

    public function safetyAcknowledgements(): HasMany
    {
        return $this->hasMany(MedicationSafetyAcknowledgement::class, 'medication_order_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
