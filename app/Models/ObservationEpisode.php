<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonInterface|null $next_monitoring_due_at
 */
class ObservationEpisode extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'reason',
        'status', // planned, active, completed, transferred, cancelled, entered_in_error
        'started_at',
        'started_by_id',
        'responsible_officer_id',
        'location_label',
        'bed_label',
        'monitoring_interval_minutes',
        'next_monitoring_due_at',
        'ended_at',
        'ended_by_id',
        'outcome',
        'outcome_reason',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'next_monitoring_due_at' => 'datetime',
            'monitoring_interval_minutes' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_id');
    }

    public function responsibleOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_officer_id');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(ObservationRecord::class, 'observation_episode_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ObservationHandover::class, 'observation_episode_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['planned', 'active'], true);
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && $this->next_monitoring_due_at instanceof CarbonInterface && $this->next_monitoring_due_at->isPast();
    }
}
