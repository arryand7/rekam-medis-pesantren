<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Return-to-activity recommendation and restriction order.
 *
 * @property string $id
 * @property string $visit_discharge_id
 * @property string $activity_status
 * @property Carbon $effective_start
 * @property Carbon|null $effective_until
 * @property string $restriction_type
 * @property string $restriction_notes
 * @property string|null $allowed_activity_notes
 * @property string $issued_by_id
 * @property Carbon $issued_at
 * @property Carbon|null $review_date
 * @property string $status
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityRestriction extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'visit_discharge_id',
        'activity_status',
        'effective_start',
        'effective_until',
        'restriction_type',
        'restriction_notes',
        'allowed_activity_notes',
        'issued_by_id',
        'issued_at',
        'review_date',
        'status',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'effective_start' => 'datetime',
            'effective_until' => 'datetime',
            'issued_at' => 'datetime',
            'review_date' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function visitDischarge(): BelongsTo
    {
        return $this->belongsTo(VisitDischarge::class, 'visit_discharge_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_until !== null && now()->isAfter($this->effective_until)) {
            return false;
        }

        return true;
    }
}
