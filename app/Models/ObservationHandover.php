<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationHandover extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'observation_episode_id',
        'from_user_id',
        'to_user_id',
        'prepared_at',
        'summary',
        'current_condition',
        'pending_tasks',
        'risks_or_warnings',
        'next_monitoring_due_at',
        'status', // draft, submitted, acknowledged, cancelled, entered_in_error
        'submitted_at',
        'acknowledged_at',
        'acknowledged_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'prepared_at' => 'datetime',
            'submitted_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'next_monitoring_due_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(ObservationEpisode::class, 'observation_episode_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_id');
    }
}
