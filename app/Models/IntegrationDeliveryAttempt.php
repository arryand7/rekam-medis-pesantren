<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $outbox_event_id
 * @property int $attempt_number
 * @property string $destination
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property string $result
 * @property string|null $external_reference
 * @property int|null $http_status_code
 * @property string|null $sanitized_error
 * @property int|null $latency_ms
 * @property string $correlation_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class IntegrationDeliveryAttempt extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'outbox_event_id',
        'attempt_number',
        'destination',
        'started_at',
        'finished_at',
        'result',
        'external_reference',
        'http_status_code',
        'sanitized_error',
        'latency_ms',
        'correlation_id',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'http_status_code' => 'integer',
        'latency_ms' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function outboxEvent(): BelongsTo
    {
        return $this->belongsTo(IntegrationOutboxEvent::class, 'outbox_event_id');
    }
}
