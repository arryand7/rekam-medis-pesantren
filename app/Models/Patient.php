<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'person_id',
        'patient_number',
        'is_eligible',
        'ineligibility_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function healthProfile(): HasOne
    {
        return $this->hasOne(PatientHealthProfile::class, 'patient_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id');
    }

    public function activeAllergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id')->whereIn('status', ['suspected', 'confirmed']);
    }

    public function medicalConditions(): HasMany
    {
        return $this->hasMany(PatientMedicalCondition::class, 'patient_id');
    }

    public function activeMedicalConditions(): HasMany
    {
        return $this->hasMany(PatientMedicalCondition::class, 'patient_id')->where('status', 'active');
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(PatientEmergencyContact::class, 'patient_id')->where('is_active', true)->orderBy('priority');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class, 'patient_id');
    }

    public function activeVisit(): HasOne
    {
        return $this->hasOne(MedicalVisit::class, 'patient_id')->whereIn('status', ['registered', 'waiting_assessment']);
    }

    /**
     * Generate server-side authoritative patient number.
     */
    public static function generatePatientNumber(): string
    {
        $prefix = 'PAT-'.date('Ymd');
        $random = strtoupper(Str::random(5));

        return "{$prefix}-{$random}";
    }
}
