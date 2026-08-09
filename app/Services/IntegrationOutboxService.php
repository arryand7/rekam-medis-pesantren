<?php

namespace App\Services;

use App\Contracts\Integration\AttendanceIntegrationContract;
use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\IntegrationDeliveryAttempt;
use App\Models\IntegrationIdentityConflict;
use App\Models\IntegrationOutboxEvent;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Service managing transactional integration outbox events, delivery workers,
 * retry/backoff policies, dead-lettering, and identity conflicts.
 */
class IntegrationOutboxService
{
    public function __construct(
        protected ?AttendanceIntegrationContract $attendanceClient = null
    ) {}

    protected function getAttendanceClient(): AttendanceIntegrationContract
    {
        return $this->attendanceClient ?? app(AttendanceIntegrationContract::class);
    }

    /**
     * Create an outbox event within the current database transaction.
     *
     * @param  array<string, mixed>  $payload
     */
    public function createOutboxEvent(
        string $eventType,
        string $aggregateType,
        string $aggregateId,
        string $destination,
        array $payload,
        string $idempotencyKey,
        ?string $correlationId = null,
        ?User $actor = null
    ): IntegrationOutboxEvent {
        $actor = $actor ?? Auth::user();
        $correlationId = $correlationId ?? (string) Str::ulid();

        $event = IntegrationOutboxEvent::create([
            'event_type' => $eventType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'destination' => $destination,
            'payload_snapshot' => $payload,
            'payload_version' => 1,
            'idempotency_key' => $idempotencyKey,
            'status' => 'pending',
            'available_at' => now(),
            'attempt_count' => 0,
            'correlation_id' => $correlationId,
            'created_by_id' => $actor?->id,
        ]);

        AuditLogService::log(
            action: 'integration_outbox.created',
            subjectType: 'IntegrationOutboxEvent',
            subjectId: $event->id,
            before: null,
            after: [
                'event_type' => $event->event_type,
                'destination' => $event->destination,
                'idempotency_key' => $event->idempotency_key,
            ],
            reason: "Event outbox {$event->event_type} untuk {$event->destination} dibuat"
        );

        return $event;
    }

    /**
     * Process pending or retryable outbox events with row locking.
     *
     * @return array{processed: int, succeeded: int, failed: int, dead_lettered: int}
     */
    public function processPendingEvents(int $batchSize = 25): array
    {
        $metrics = [
            'processed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'dead_lettered' => 0,
        ];

        // Fetch candidate event IDs without long-lived table lock
        $candidateIds = IntegrationOutboxEvent::whereIn('status', ['pending', 'failed'])
            ->where('available_at', '<=', now())
            ->orderBy('available_at')
            ->limit($batchSize)
            ->pluck('id');

        foreach ($candidateIds as $eventId) {
            $result = $this->processSingleEvent($eventId);
            $metrics['processed']++;
            if ($result === 'success') {
                $metrics['succeeded']++;
            } elseif ($result === 'dead_letter') {
                $metrics['dead_lettered']++;
            } else {
                $metrics['failed']++;
            }
        }

        return $metrics;
    }

    /**
     * Process a single outbox event under an isolated transaction lock.
     */
    public function processSingleEvent(string $eventId): string
    {
        return DB::transaction(function () use ($eventId) {
            /** @var IntegrationOutboxEvent|null $event */
            $event = IntegrationOutboxEvent::where('id', $eventId)->lockForUpdate()->first();

            if (! $event || ! in_array($event->status, ['pending', 'failed'])) {
                return 'skipped';
            }

            $event->update([
                'status' => 'processing',
                'last_attempt_at' => now(),
                'attempt_count' => $event->attempt_count + 1,
            ]);

            $attemptNumber = $event->attempt_count;
            $startTime = microtime(true);
            $startedAt = now();

            try {
                $deliveryResult = $this->dispatchToDestination($event);
                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                if ($deliveryResult['success']) {
                    $event->update([
                        'status' => 'acknowledged',
                        'sent_at' => now(),
                        'acknowledged_at' => now(),
                        'last_error_code' => null,
                        'last_error_message_sanitized' => null,
                    ]);

                    IntegrationDeliveryAttempt::create([
                        'outbox_event_id' => $event->id,
                        'attempt_number' => $attemptNumber,
                        'destination' => $event->destination,
                        'started_at' => $startedAt,
                        'finished_at' => now(),
                        'result' => 'success',
                        'external_reference' => $deliveryResult['external_reference'],
                        'http_status_code' => $deliveryResult['status_code'] ?? 200,
                        'latency_ms' => $latencyMs,
                        'correlation_id' => $event->correlation_id,
                    ]);

                    AuditLogService::log(
                        action: 'integration_delivery.succeeded',
                        subjectType: 'IntegrationOutboxEvent',
                        subjectId: $event->id,
                        before: null,
                        after: ['attempt' => $attemptNumber, 'latency_ms' => $latencyMs],
                        reason: "Pengiriman outbox {$event->event_type} ke {$event->destination} berhasil"
                    );

                    return 'success';
                } else {
                    return $this->handleFailure($event, $attemptNumber, $startedAt, $latencyMs, $deliveryResult['error'] ?? 'Unknown error', $deliveryResult['status_code'] ?? 500);
                }
            } catch (Throwable $e) {
                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                return $this->handleFailure($event, $attemptNumber, $startedAt, $latencyMs, $e->getMessage(), 500);
            }
        });
    }

    /**
     * Dispatch payload to the resolved destination adapter.
     *
     * @return array{success: bool, external_reference: ?string, status_code: ?int, error: ?string}
     */
    protected function dispatchToDestination(IntegrationOutboxEvent $event): array
    {
        if ($event->destination === 'attendance_system') {
            $client = $this->getAttendanceClient();
            $dto = AttendanceHealthDispositionDTO::fromArray($event->payload_snapshot);

            if ($event->event_type === 'health_disposition_superseded' && ! empty($dto->supersedesEventId)) {
                return $client->supersedeDisposition($dto->supersedesEventId, $dto);
            }

            return $client->publishDisposition($dto);
        }

        // Generic destination mock
        return [
            'success' => true,
            'external_reference' => 'GEN-'.Str::random(8),
            'status_code' => 200,
            'error' => null,
        ];
    }

    /**
     * Handle delivery failure with exponential backoff or dead-letter transition.
     */
    protected function handleFailure(
        IntegrationOutboxEvent $event,
        int $attemptNumber,
        Carbon $startedAt,
        int $latencyMs,
        string $errorMessage,
        int $statusCode
    ): string {
        $maxAttempts = (int) config('integration.outbox.max_attempts', 5);
        $baseBackoff = (int) config('integration.attendance.retry_backoff_seconds', 60);

        $sanitizedError = substr($errorMessage, 0, 1000);
        $isDeadLetter = $attemptNumber >= $maxAttempts;

        $newStatus = $isDeadLetter ? 'dead_letter' : 'failed';
        $nextAvailableAt = now()->addSeconds($baseBackoff * ($attemptNumber ** 2));

        $event->update([
            'status' => $newStatus,
            'failed_at' => now(),
            'available_at' => $nextAvailableAt,
            'last_error_code' => 'ERR_'.$statusCode,
            'last_error_message_sanitized' => $sanitizedError,
        ]);

        IntegrationDeliveryAttempt::create([
            'outbox_event_id' => $event->id,
            'attempt_number' => $attemptNumber,
            'destination' => $event->destination,
            'started_at' => $startedAt,
            'finished_at' => now(),
            'result' => $isDeadLetter ? 'dead_lettered' : 'transient_failure',
            'http_status_code' => $statusCode,
            'sanitized_error' => $sanitizedError,
            'latency_ms' => $latencyMs,
            'correlation_id' => $event->correlation_id,
        ]);

        AuditLogService::log(
            action: $isDeadLetter ? 'integration_delivery.dead_lettered' : 'integration_delivery.failed',
            subjectType: 'IntegrationOutboxEvent',
            subjectId: $event->id,
            before: null,
            after: ['attempt' => $attemptNumber, 'status' => $newStatus, 'error' => $sanitizedError],
            reason: $isDeadLetter
                ? "Event outbox {$event->id} dipindahkan ke dead-letter setelah {$attemptNumber} percobaan gagal"
                : "Percobaan ke-{$attemptNumber} outbox {$event->id} gagal: {$sanitizedError}"
        );

        return $isDeadLetter ? 'dead_letter' : 'failed';
    }

    /**
     * Manually retry a failed or dead-lettered outbox event.
     */
    public function retryEvent(IntegrationOutboxEvent $event, ?User $actor = null): IntegrationOutboxEvent
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($event, $actor) {
            /** @var IntegrationOutboxEvent $locked */
            $locked = IntegrationOutboxEvent::where('id', $event->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'pending',
                'available_at' => now(),
                'last_error_message_sanitized' => null,
            ]);

            AuditLogService::log(
                action: 'integration_outbox.retried',
                subjectType: 'IntegrationOutboxEvent',
                subjectId: $locked->id,
                before: null,
                after: ['status' => 'pending'],
                reason: 'Event outbox di-retry secara manual oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            return $locked;
        });
    }

    /**
     * Record an identity conflict when Gate User ID is missing or mismatched.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function recordIdentityConflict(
        Person $person,
        string $destination,
        string $conflictType,
        array $snapshot,
        ?string $correlationId = null
    ): IntegrationIdentityConflict {
        $correlationId = $correlationId ?? (string) Str::ulid();

        $conflict = IntegrationIdentityConflict::create([
            'person_id' => $person->id,
            'destination' => $destination,
            'conflict_type' => $conflictType,
            'source_identifier_snapshot' => $snapshot,
            'status' => 'open',
            'correlation_id' => $correlationId,
        ]);

        AuditLogService::log(
            action: 'integration_identity_conflict.created',
            subjectType: 'IntegrationIdentityConflict',
            subjectId: $conflict->id,
            before: null,
            after: $conflict->toArray(),
            reason: "Konflik identitas integrasi ({$conflictType}) tercatat untuk {$person->full_name}"
        );

        return $conflict;
    }

    /**
     * Resolve an identity conflict manually.
     */
    public function resolveConflict(
        IntegrationIdentityConflict $conflict,
        string $resolutionNotes,
        ?User $actor = null
    ): IntegrationIdentityConflict {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($conflict, $resolutionNotes, $actor) {
            /** @var IntegrationIdentityConflict $locked */
            $locked = IntegrationIdentityConflict::where('id', $conflict->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'resolved',
                'resolution_notes' => trim($resolutionNotes),
                'resolved_by_id' => $actor?->id,
                'resolved_at' => now(),
            ]);

            AuditLogService::log(
                action: 'integration_identity_conflict.resolved',
                subjectType: 'IntegrationIdentityConflict',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: 'Konflik identitas integrasi diselesaikan oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            return $locked;
        });
    }
}
