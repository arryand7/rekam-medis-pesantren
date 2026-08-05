<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthcarePartnerContact extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'healthcare_partner_id',
        'name',
        'profession',
        'registration_identifier',
        'department',
        'official_contact',
        'channel_type',
        'is_active',
        'verified_at',
        'verified_by_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }
}
