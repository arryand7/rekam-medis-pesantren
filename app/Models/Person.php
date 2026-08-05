<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'people';

    protected $fillable = [
        'gate_user_id',
        'name',
        'nik',
        'nis_nip',
        'user_type',
        'gender',
        'phone',
        'email',
        'source_status',
        'source_updated_at',
        'source_version',
        'checksum',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'source_updated_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'person_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'person_id');
    }

    /**
     * Check if person is a human eligible to become a patient.
     * Rule: All humans are eligible (santri, guru, staf, pengasuh, health workers, admins).
     * Only bots, service accounts, or pure technical accounts are not eligible.
     */
    public function isHumanPatientEligible(): bool
    {
        return ! in_array(strtolower($this->user_type), ['service_account', 'bot', 'technical_account'], true);
    }
}
