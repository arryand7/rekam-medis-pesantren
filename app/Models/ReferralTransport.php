<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralTransport extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'transport_type', // school_vehicle, ambulance_partner, external_ambulance, private_vehicle, other
        'vehicle_identifier',
        'driver_name',
        'driver_contact',
        'arranged_by_id',
        'arranged_at',
        'departure_planned',
        'departure_actual',
        'arrival_actual',
        'status', // planned, ready, departed, arrived, cancelled
        'notes',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'arranged_at' => 'datetime',
            'departure_planned' => 'datetime',
            'departure_actual' => 'datetime',
            'arrival_actual' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function arrangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arranged_by_id');
    }
}
