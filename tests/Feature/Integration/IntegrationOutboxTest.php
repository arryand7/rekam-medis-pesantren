<?php

use App\Contracts\Integration\AttendanceIntegrationContract;
use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\IntegrationDeliveryAttempt;
use App\Models\IntegrationOutboxEvent;
use App\Models\User;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\IntegrationOutboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    FakeAttendanceIntegration::reset();
});

test('outbox event is created transactionally and rolls back if transaction fails', function () {
    $service = new IntegrationOutboxService;
    $user = User::factory()->create();

    try {
        DB::transaction(function () use ($service, $user) {
            $service->createOutboxEvent(
                eventType: 'health_disposition_published',
                aggregateType: 'VisitDischarge',
                aggregateId: (string) Str::ulid(),
                destination: 'attendance_system',
                payload: ['dummy' => 'payload'],
                idempotencyKey: 'IDEMP-TEST-ROLLBACK',
                actor: $user
            );

            throw new Exception('Simulated business failure');
        });
    } catch (Exception $e) {
        // Expected
    }

    $exists = IntegrationOutboxEvent::where('idempotency_key', 'IDEMP-TEST-ROLLBACK')->exists();
    expect($exists)->toBeFalse();
});

test('outbox event enforces unique idempotency key per destination', function () {
    $service = new IntegrationOutboxService;
    $user = User::factory()->create();

    $service->createOutboxEvent(
        eventType: 'health_disposition_published',
        aggregateType: 'VisitDischarge',
        aggregateId: (string) Str::ulid(),
        destination: 'attendance_system',
        payload: ['dummy' => 'payload'],
        idempotencyKey: 'IDEMP-TEST-UNIQUE',
        actor: $user
    );

    expect(function () use ($service, $user) {
        $service->createOutboxEvent(
            eventType: 'health_disposition_published',
            aggregateType: 'VisitDischarge',
            aggregateId: (string) Str::ulid(),
            destination: 'attendance_system',
            payload: ['dummy' => 'payload'],
            idempotencyKey: 'IDEMP-TEST-UNIQUE',
            actor: $user
        );
    })->toThrow(Exception::class);
});

test('worker processes pending event and transitions to acknowledged with delivery attempt', function () {
    $service = new IntegrationOutboxService;
    $user = User::factory()->create();

    $event = $service->createOutboxEvent(
        eventType: 'health_disposition_published',
        aggregateType: 'VisitDischarge',
        aggregateId: (string) Str::ulid(),
        destination: 'attendance_system',
        payload: [
            'event_id' => (string) Str::ulid(),
            'event_version' => 1,
            'gate_user_id' => 'GATE-USER-123',
            'disposition_type' => 'rest',
            'effective_from' => now()->toIso8601String(),
            'effective_until' => now()->addDays(2)->toIso8601String(),
            'activity_scope' => 'all_activities',
            'source_visit_reference' => 'VISIT-001',
            'issued_at' => now()->toIso8601String(),
        ],
        idempotencyKey: 'IDEMP-WORKER-SUCCESS',
        actor: $user
    );

    expect($event->status)->toBe('pending');

    $result = $service->processPendingEvents();

    expect($result['processed'])->toBe(1);
    expect($result['succeeded'])->toBe(1);

    $freshEvent = $event->fresh();
    expect($freshEvent->status)->toBe('acknowledged');
    expect($freshEvent->sent_at)->not->toBeNull();
    expect($freshEvent->acknowledged_at)->not->toBeNull();

    $attempt = IntegrationDeliveryAttempt::where('outbox_event_id', $event->id)->first();
    expect($attempt)->not->toBeNull();
    expect($attempt->result)->toBe('success');
});

test('transient failures trigger retry backoff and dead-letter after max attempts', function () {
    // Create mock client that fails
    $failingClient = new class implements AttendanceIntegrationContract
    {
        public function publishDisposition(AttendanceHealthDispositionDTO $dto): array
        {
            return ['success' => false, 'external_reference' => null, 'status_code' => 503, 'error' => 'Service Unavailable'];
        }

        public function supersedeDisposition(string $originalEventId, AttendanceHealthDispositionDTO $newDto): array
        {
            return ['success' => false, 'external_reference' => null, 'status_code' => 503, 'error' => 'Service Unavailable'];
        }

        public function revokeDisposition(string $eventId, string $reason): array
        {
            return ['success' => false, 'external_reference' => null, 'status_code' => 503, 'error' => 'Service Unavailable'];
        }

        public function probeHealth(): array
        {
            return ['driver' => 'mock', 'enabled' => true, 'reachable' => false, 'message' => 'Down'];
        }
    };

    $service = new IntegrationOutboxService($failingClient);
    $user = User::factory()->create();

    config(['integration.outbox.max_attempts' => 2]);

    $event = $service->createOutboxEvent(
        eventType: 'health_disposition_published',
        aggregateType: 'VisitDischarge',
        aggregateId: (string) Str::ulid(),
        destination: 'attendance_system',
        payload: [
            'event_id' => (string) Str::ulid(),
            'event_version' => 1,
            'gate_user_id' => 'GATE-USER-FAIL',
            'disposition_type' => 'rest',
            'effective_from' => now()->toIso8601String(),
            'activity_scope' => 'all_activities',
            'source_visit_reference' => 'VISIT-FAIL',
            'issued_at' => now()->toIso8601String(),
        ],
        idempotencyKey: 'IDEMP-WORKER-FAIL',
        actor: $user
    );

    // Attempt 1: marks failed, schedules retry
    $service->processPendingEvents();
    $fresh = $event->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->attempt_count)->toBe(1);

    // Force available_at to now for attempt 2
    $fresh->update(['available_at' => now()]);

    // Attempt 2: hits max_attempts (2) -> dead_letter
    $service->processPendingEvents();
    $freshAfterDeadLetter = $event->fresh();
    expect($freshAfterDeadLetter->status)->toBe('dead_letter');
    expect($freshAfterDeadLetter->attempt_count)->toBe(2);
});
