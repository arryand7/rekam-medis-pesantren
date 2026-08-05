<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'version_number',
        'summary_payload',
        'checksum',
        'authored_by_id',
        'finalized_at',
        'supersedes_version_id',
        'redaction_notes',
    ];

    protected function casts(): array
    {
        return [
            'summary_payload' => 'array',
            'version_number' => 'integer',
            'finalized_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function authoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authored_by_id');
    }
}
