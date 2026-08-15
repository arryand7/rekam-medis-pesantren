<?php

use App\Models\IntegrationOutboxEvent;
use Illuminate\Support\Str;

test('scheduled outbox command processes pending events', function () {
    $event = IntegrationOutboxEvent::create([
        'event_type' => 'health_disposition_published',
        'aggregate_type' => 'VisitDischarge',
        'aggregate_id' => 'DISC-COMMAND-001',
        'destination' => 'attendance_system',
        'payload_snapshot' => [
            'event_id' => 'EVT-COMMAND-001',
            'event_version' => 1,
            'gate_user_id' => 'GATE-COMMAND-001',
            'disposition_type' => 'rest',
            'effective_from' => now()->toIso8601String(),
            'effective_until' => now()->addDay()->toIso8601String(),
            'activity_scope' => 'all_activities',
            'source_visit_reference' => 'VIS-COMMAND-001',
            'issued_at' => now()->toIso8601String(),
        ],
        'payload_version' => 1,
        'idempotency_key' => 'COMMAND-001',
        'status' => 'pending',
        'available_at' => now(),
        'correlation_id' => (string) Str::ulid(),
    ]);

    $this->artisan('integration:outbox:process', ['--batch' => 10])
        ->expectsOutputToContain('processed=1 succeeded=1 failed=0 dead_lettered=0')
        ->assertSuccessful();

    expect($event->fresh()->status)->toBe('acknowledged')
        ->and($event->fresh()->deliveryAttempts)->toHaveCount(1);
});

test('outbox command rejects unsafe batch values', function () {
    $this->artisan('integration:outbox:process', ['--batch' => 0])
        ->expectsOutputToContain('batch size must be between 1 and 500')
        ->assertExitCode(2);
});
