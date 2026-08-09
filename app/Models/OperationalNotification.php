<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $person_id
 * @property string|null $patient_id
 * @property string|null $medical_visit_id
 * @property string|null $visit_discharge_id
 * @property string|null $activity_restriction_id
 * @property string $notification_type
 * @property string $recipient_type
 * @property string|null $recipient_reference
 * @property array<string, mixed> $payload_snapshot
 * @property string $priority
 * @property string $status
 * @property string|null $prepared_by_id
 * @property Carbon $prepared_at
 * @property Carbon|null $ready_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $acknowledged_at
 * @property string|null $acknowledged_by_id
 * @property string|null $acknowledgement_notes
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property string $correlation_id
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OperationalNotification extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'person_id',
        'patient_id',
        'medical_visit_id',
        'visit_discharge_id',
        'activity_restriction_id',
        'notification_type',
        'recipient_type',
        'recipient_reference',
        'payload_snapshot',
        'priority',
        'status',
        'prepared_by_id',
        'prepared_at',
        'ready_at',
        'delivered_at',
        'acknowledged_at',
        'acknowledged_by_id',
        'acknowledgement_notes',
        'cancelled_at',
        'cancellation_reason',
        'correlation_id',
        'lock_version',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'prepared_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'lock_version' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function visitDischarge(): BelongsTo
    {
        return $this->belongsTo(VisitDischarge::class, 'visit_discharge_id');
    }

    public function activityRestriction(): BelongsTo
    {
        return $this->belongsTo(ActivityRestriction::class, 'activity_restriction_id');
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
        return $this->status === 'acknowledged';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
