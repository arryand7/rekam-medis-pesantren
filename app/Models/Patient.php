<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'person_id',
        'patient_number',
        'is_eligible',
        'ineligibility_reason',
        'blood_type',
        'allergies_summary',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
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
