<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'recorded_at',
        'recorded_by_id',
        'temperature_c',
        'systolic_bp',
        'diastolic_bp',
        'pulse_bpm',
        'respiratory_rate',
        'spo2_percent',
        'weight_kg',
        'height_cm',
        'pain_score',
        'consciousness_level',
        'notes',
        'status',
        'finalized_at',
        'finalized_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'finalized_at' => 'datetime',
            'temperature_c' => 'float',
            'weight_kg' => 'float',
            'height_cm' => 'float',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by_id');
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }
}
