<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthcarePartner extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'code',
        'name',
        'partner_type', // puskesmas, hospital, clinic, other
        'address',
        'phone',
        'official_email',
        'cooperation_reference',
        'is_active',
        'consultation_enabled',
        'referral_enabled',
        'default_channel',
        'created_by_id',
        'updated_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'consultation_enabled' => 'boolean',
            'referral_enabled' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(HealthcarePartnerContact::class, 'healthcare_partner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
