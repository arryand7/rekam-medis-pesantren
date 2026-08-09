<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Internal operational handoff for care instructions following visit discharge.
 *
 * Enforces the minimum-necessary privacy principle: only practical care, activity,
 * and restriction instructions are shared — never raw medical narratives or diagnostic musings.
 *
 * @property string $id
 * @property string $medical_visit_id
 * @property string $visit_discharge_id
 * @property string $recipient_type
 * @property string|null $recipient_reference
 * @property string $purpose
 * @property array<string, mixed> $payload_snapshot
 * @property string $status
 * @property string $prepared_by_id
 * @property Carbon $prepared_at
 * @property Carbon|null $acknowledged_at
 * @property string|null $acknowledged_by_id
 * @property string|null $acknowledgement_notes
 * @property string $channel
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ClinicalOperationalHandoff extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'visit_discharge_id',
        'recipient_type',
        'recipient_reference',
        'purpose',
        'payload_snapshot',
        'status',
        'prepared_by_id',
        'prepared_at',
        'acknowledged_at',
        'acknowledged_by_id',
        'acknowledgement_notes',
        'channel',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'payload_snapshot' => 'array',
            'prepared_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function visitDischarge(): BelongsTo
    {
        return $this->belongsTo(VisitDischarge::class, 'visit_discharge_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_id');
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged' && $this->acknowledged_at !== null;
    }
}
