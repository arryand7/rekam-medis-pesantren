<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalCondition extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'patient_id',
        'condition_name',
        'status',
        'onset_date',
        'notes',
        'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'onset_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
