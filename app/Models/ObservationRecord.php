<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationRecord extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'observation_episode_id',
        'recorded_at',
        'recorded_by_id',
        'condition_summary',
        'symptom_changes',
        'general_condition',
        'vital_sign_id',
        'fluid_intake_note',
        'food_intake_note',
        'elimination_note',
        'activity_or_rest_note',
        'follow_up_note',
        'status',
        'finalized_at',
        'finalized_by_id',
        'parent_record_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'finalized_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(ObservationEpisode::class, 'observation_episode_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function vitalSign(): BelongsTo
    {
        return $this->belongsTo(VitalSign::class, 'vital_sign_id');
    }
}
