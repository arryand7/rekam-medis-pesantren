<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $event_type
 * @property string $aggregate_type
 * @property string $aggregate_id
 * @property string $destination
 * @property array<string, mixed> $payload_snapshot
 * @property int $payload_version
 * @property string $idempotency_key
 * @property string $status
 * @property Carbon $available_at
 * @property int $attempt_count
 * @property Carbon|null $last_attempt_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $acknowledged_at
 * @property Carbon|null $failed_at
 * @property string|null $last_error_code
 * @property string|null $last_error_message_sanitized
 * @property string $correlation_id
 * @property string|null $created_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class IntegrationOutboxEvent extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'destination',
        'payload_snapshot',
        'payload_version',
        'idempotency_key',
        'status',
        'available_at',
        'attempt_count',
        'last_attempt_at',
        'sent_at',
        'acknowledged_at',
        'failed_at',
        'last_error_code',
        'last_error_message_sanitized',
        'correlation_id',
        'created_by_id',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'payload_version' => 'integer',
        'attempt_count' => 'integer',
        'available_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function deliveryAttempts(): HasMany
    {
        return $this->hasMany(IntegrationDeliveryAttempt::class, 'outbox_event_id')->orderBy('attempt_number');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isDeadLetter(): bool
    {
        return $this->status === 'dead_letter';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
