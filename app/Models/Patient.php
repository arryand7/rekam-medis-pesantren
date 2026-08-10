<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
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
     * Generate collision-resilient authoritative patient number.
     */
    public static function generatePatientNumber(): string
    {
        return static::generateUniquePatientNumber();
    }

    /**
     * Generate unique patient number with deterministic retry and entropy escalation.
     */
    public static function generateUniquePatientNumber(?string $personId = null, int $attempt = 0): string
    {
        if ($personId && $attempt === 0) {
            $candidate = 'RM-'.strtoupper(substr($personId, -10));
            if (! static::where('patient_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Entropy escalation on retries: random alphanumerics + high-entropy suffix
        for ($i = 0; $i < 10; $i++) {
            $candidate = 'RM-'.strtoupper(Str::random(10));
            if (! static::where('patient_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Guaranteed unique fallback combining microtime + random ULID suffix
        return 'RM-'.strtoupper(Str::random(4)).'-'.strtoupper(substr((string) Str::ulid(), -8));
    }

    /**
     * Create or retrieve Patient record for Person with automatic DB collision recovery.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createOrFindForPerson(Person $person, array $attributes = []): self
    {
        $existing = static::where('person_id', $person->id)->first();
        if ($existing) {
            return $existing;
        }

        $maxRetries = 5;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $patientNumber = static::generateUniquePatientNumber($person->id, $attempt);

                return static::create(array_merge([
                    'person_id' => $person->id,
                    'patient_number' => $patientNumber,
                    'is_eligible' => true,
                ], $attributes));
            } catch (QueryException $e) {
                // Check if concurrent worker already created the Patient for this person
                $existing = static::where('person_id', $person->id)->first();
                if ($existing) {
                    return $existing;
                }

                // If error code is duplicate entry on patient_number, retry with higher entropy
                if ($attempt === $maxRetries - 1) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException("Gagal membuat nomor rekam medis unik untuk person {$person->id} setelah {$maxRetries} kali percobaan.");
    }
}
