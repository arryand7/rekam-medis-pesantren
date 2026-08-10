<?php

use App\Models\IntegrationOutboxEvent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Integration\HttpAttendanceSandboxIntegration;
use App\Services\IntegrationOutboxService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function createOutboxAdmin(): User
{
    $user = User::factory()->create();
    $role = Role::create(['name' => 'outbox_admin_'.uniqid(), 'display_name' => 'Outbox Admin']);

    $p1 = Permission::firstOrCreate(['name' => 'view-integration-outbox'], ['display_name' => 'View Outbox']);
    $p2 = Permission::firstOrCreate(['name' => 'retry-integration-events'], ['display_name' => 'Retry Outbox']);

    $role->permissions()->syncWithoutDetaching([$p1->id, $p2->id]);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $user->unsetRelation('roles');

    return $user;

}

test('outbox transitions to failed with backoff and finally dead_letter on max attempts exhausted', function () {
    Http::fake([
        'https://absensi-sandbox.sabira.id/api/v1/health-dispositions' => Http::response('Service Unavailable', 503),
    ]);

    $service = new IntegrationOutboxService(new HttpAttendanceSandboxIntegration);

    $event = IntegrationOutboxEvent::create([
        'event_type' => 'health_disposition_published',
        'aggregate_type' => 'VisitDischarge',
        'aggregate_id' => 'DISC-001',
        'destination' => 'attendance_system',
        'payload_snapshot' => [
            'event_id' => 'EVT-FAIL-001',
            'event_version' => 1,
            'gate_user_id' => 'GATE-USR-001',
            'disposition_type' => 'rest',
            'effective_from' => '2026-08-10',
            'effective_until' => '2026-08-12',
            'activity_scope' => 'all_activities',
            'source_visit_reference' => 'VIS-001',
            'issued_at' => now()->toIso8601String(),
        ],
        'payload_version' => 1,
        'idempotency_key' => 'ABS-KEY-FAIL-001',
        'status' => 'pending',
        'attempt_count' => 4, // 5th attempt will exceed max attempts (5)
        'available_at' => now(),
        'correlation_id' => (string) Str::ulid(),
    ]);

    $result = $service->processSingleEvent($event->id);

    expect($result)->toBe('dead_letter');
    $event->refresh();
    expect($event->status)->toBe('dead_letter');
    expect($event->attempt_count)->toBe(5);
    expect($event->last_error_message_sanitized)->toContain('503');
});

test('authorized admin can manually retry dead_letter or failed outbox event', function () {
    $admin = createOutboxAdmin();
    $this->actingAs($admin);

    $event = IntegrationOutboxEvent::create([
        'event_type' => 'health_disposition_published',
        'aggregate_type' => 'VisitDischarge',
        'aggregate_id' => 'DISC-002',
        'destination' => 'attendance_system',
        'payload_snapshot' => [
            'event_id' => 'EVT-RETRY-001',
            'event_version' => 1,
            'gate_user_id' => 'GATE-USR-002',
            'disposition_type' => 'rest',
            'effective_from' => '2026-08-10',
            'effective_until' => '2026-08-12',
            'activity_scope' => 'all_activities',
            'source_visit_reference' => 'VIS-002',
            'issued_at' => now()->toIso8601String(),
        ],
        'payload_version' => 1,
        'idempotency_key' => 'ABS-KEY-RETRY-001',
        'status' => 'dead_letter',
        'attempt_count' => 5,
        'available_at' => now(),
        'correlation_id' => (string) Str::ulid(),
    ]);

    $response = $this->post("/integration/outbox/{$event->id}/retry", [
        'reason' => 'Retry manual setelah endpoint upstream pulih',
    ]);

    $response->assertRedirect();
    $event->refresh();
    expect($event->status)->toBe('pending');
    expect($event->last_error_message_sanitized)->toBeNull();
});

test('unauthorized user cannot retry outbox event (403 forbidden)', function () {
    $user = User::factory()->create(); // No retry permission
    $this->actingAs($user);

    $event = IntegrationOutboxEvent::create([
        'event_type' => 'health_disposition_published',
        'aggregate_type' => 'VisitDischarge',
        'aggregate_id' => 'DISC-003',
        'destination' => 'attendance_system',
        'payload_snapshot' => ['event_id' => 'EVT-FORBIDDEN-001'],
        'payload_version' => 1,
        'idempotency_key' => 'ABS-KEY-003',
        'status' => 'dead_letter',
        'available_at' => now(),
        'correlation_id' => (string) Str::ulid(),
    ]);

    $response = $this->post("/integration/outbox/{$event->id}/retry", [
        'reason' => 'Unauthorized retry',
    ]);

    $response->assertForbidden();
});

test('attendance health status probe endpoint returns driver status', function () {
    $admin = createOutboxAdmin();
    $p = Permission::firstOrCreate(['name' => 'view-attendance-integration-status'], ['display_name' => 'View Attendance Status']);
    $admin->roles->first()->permissions()->syncWithoutDetaching([$p->id]);

    $this->actingAs($admin);

    $response = $this->get('/integration/attendance/status');
    $response->assertOk();
    $response->assertSee('SABIRA Absensi');
});
