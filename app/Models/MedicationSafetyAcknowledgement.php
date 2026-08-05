<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationSafetyAcknowledgement extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'patient_id',
        'medical_visit_id',
        'medication_order_id',
        'warning_type',
        'allergy_reference_id',
        'warning_snapshot',
        'acknowledged_by_id',
        'acknowledged_at',
        'reason',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function medicationOrder(): BelongsTo
    {
        return $this->belongsTo(MedicationOrder::class, 'medication_order_id');
    }

    public function allergyReference(): BelongsTo
    {
        return $this->belongsTo(PatientAllergy::class, 'allergy_reference_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_id');
    }
}
