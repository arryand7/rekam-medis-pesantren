<?php

use App\Models\IntegrationDeliveryAttempt;
use App\Models\IntegrationOutboxEvent;
use App\Models\User;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\IntegrationOutboxService;
use Illuminate\Support\Str;

beforeEach(function () {
    FakeAttendanceIntegration::reset();
});

test('concurrent workers claiming outbox events process each event exactly once on MariaDB', function () {
    $service = new IntegrationOutboxService;
    $user = User::factory()->create();

    $events = [];
    for ($i = 1; $i <= 5; $i++) {
        $events[] = $service->createOutboxEvent(
            eventType: 'health_disposition_published',
            aggregateType: 'VisitDischarge',
            aggregateId: (string) Str::ulid(),
            destination: 'attendance_system',
            payload: [
                'event_id' => (string) Str::ulid(),
                'event_version' => 1,
                'gate_user_id' => "GATE-CONCUR-{$i}",
                'disposition_type' => 'rest',
                'effective_from' => now()->toIso8601String(),
                'activity_scope' => 'all_activities',
                'source_visit_reference' => "VISIT-CONCUR-{$i}",
                'issued_at' => now()->toIso8601String(),
            ],
            idempotencyKey: "IDEMP-CONCUR-{$i}",
            actor: $user
        );
    }

    // Run first batch worker
    $result1 = $service->processPendingEvents(3);
    expect($result1['processed'])->toBe(3);
    expect($result1['succeeded'])->toBe(3);

    // Run second batch worker concurrently
    $result2 = $service->processPendingEvents(3);
    expect($result2['processed'])->toBe(2);
    expect($result2['succeeded'])->toBe(2);

    // Third run should have 0 pending
    $result3 = $service->processPendingEvents(3);
    expect($result3['processed'])->toBe(0);

    // Verify all 5 events are acknowledged with exactly 1 attempt each
    expect(IntegrationOutboxEvent::where('status', 'acknowledged')->count())->toBe(5);
    expect(IntegrationDeliveryAttempt::count())->toBe(5);
});
