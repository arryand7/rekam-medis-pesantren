<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAllergy extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'patient_id',
        'allergen',
        'reaction',
        'severity',
        'status',
        'notes',
        'recorded_by_id',
        'verified_by_id',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['suspected', 'confirmed'], true);
    }
}
