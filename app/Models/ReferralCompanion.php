<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCompanion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'user_id',
        'name_snapshot',
        'role_relationship',
        'phone',
        'is_primary',
        'assigned_by_id',
        'assigned_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}
