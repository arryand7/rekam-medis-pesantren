<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralStatusEvent extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'event_type', // arrived, accepted, declined, under_external_care, return_planned, returned
        'occurred_at',
        'received_at',
        'source', // manual, callback
        'facility_partner_id',
        'contact_attribution',
        'notes',
        'recorded_by_id',
        'external_reference',
        'verification_status', // unverified, verified, disputed
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function facilityPartner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'facility_partner_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
