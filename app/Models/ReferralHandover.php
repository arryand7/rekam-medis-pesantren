<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralHandover extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'referral_version_id',
        'from_user_id',
        'destination_partner_id',
        'recipient_contact_id',
        'handed_over_at',
        'acknowledged_at',
        'notes',
        'status', // prepared, handed_over, acknowledged, failed, cancelled
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'handed_over_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function referralVersion(): BelongsTo
    {
        return $this->belongsTo(ReferralVersion::class, 'referral_version_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function destinationPartner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'destination_partner_id');
    }

    public function recipientContact(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartnerContact::class, 'recipient_contact_id');
    }
}
