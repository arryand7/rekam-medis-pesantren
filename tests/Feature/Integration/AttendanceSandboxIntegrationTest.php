<?php

use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Services\Integration\HttpAttendanceSandboxIntegration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

test('publishes health disposition successfully to sandbox endpoint with correlation headers', function () {
    Http::fake([
        'https://absensi-sandbox.sabira.id/api/v1/health-dispositions' => Http::response([
            'status' => 'success',
            'data' => [
                'reference_id' => 'ABS-REF-20260810-001',
                'status' => 'acknowledged',
            ],
        ], 200),
    ]);

    $client = new HttpAttendanceSandboxIntegration;

    $dto = new AttendanceHealthDispositionDTO(
        eventId: 'EVT-DISP-001',
        eventVersion: 1,
        gateUserId: 'GATE-SAN-001',
        dispositionType: 'rest',
        effectiveFrom: Carbon::parse('2026-08-10'),
        effectiveUntil: Carbon::parse('2026-08-12'),
        activityScope: 'all_activities',
        sourceVisitReference: 'VISIT-20260810-001',
        issuedAt: Carbon::now()
    );

    $result = $client->publishDisposition($dto);

    expect($result['success'])->toBeTrue();
    expect($result['external_reference'])->toBe('ABS-REF-20260810-001');
    expect($result['status_code'])->toBe(200);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://absensi-sandbox.sabira.id/api/v1/health-dispositions'
            && $request->hasHeader('X-Poskestren-Event-Id', 'EVT-DISP-001')
            && $request->hasHeader('X-Idempotency-Key', 'EVT-DISP-001')
            && $request['gate_user_id'] === 'GATE-SAN-001'
            && $request['disposition_type'] === 'rest'
            && ! isset($request['diagnosis'])
            && ! isset($request['medicines']);
    });
});

test('supersedes existing attendance disposition via sandbox endpoint', function () {
    Http::fake([
        'https://absensi-sandbox.sabira.id/api/v1/health-dispositions/supersede' => Http::response([
            'status' => 'success',
            'data' => [
                'reference_id' => 'ABS-SUP-20260810-999',
            ],
        ], 200),
    ]);

    $client = new HttpAttendanceSandboxIntegration;

    $newDto = new AttendanceHealthDispositionDTO(
        eventId: 'EVT-DISP-002-V2',
        eventVersion: 2,
        gateUserId: 'GATE-SAN-001',
        dispositionType: 'limited_activity',
        effectiveFrom: Carbon::parse('2026-08-10'),
        effectiveUntil: Carbon::parse('2026-08-13'),
        activityScope: 'academic_only',
        sourceVisitReference: 'VISIT-20260810-001',
        issuedAt: Carbon::now(),
        supersedesEventId: 'EVT-DISP-001'
    );

    $result = $client->supersedeDisposition('EVT-DISP-001', $newDto);

    expect($result['success'])->toBeTrue();
    expect($result['external_reference'])->toBe('ABS-SUP-20260810-999');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/supersede')
            && $request->hasHeader('X-Original-Event-Id', 'EVT-DISP-001')
            && $request['supersedes_event_id'] === 'EVT-DISP-001';
    });
});

test('blocks privacy violation if forbidden clinical keys are injected in metadata', function () {
    expect(function () {
        new AttendanceHealthDispositionDTO(
            eventId: 'EVT-ERR',
            eventVersion: 1,
            gateUserId: 'GATE-SAN-001',
            dispositionType: 'rest',
            effectiveFrom: Carbon::parse('2026-08-10'),
            effectiveUntil: Carbon::parse('2026-08-12'),
            activityScope: 'all_activities',
            sourceVisitReference: 'VISIT-20260810-001',
            issuedAt: Carbon::now(),
            metadata: [
                'diagnosis' => 'Typhoid Fever',
            ]
        );
    })->toThrow(InvalidArgumentException::class);
});

test('handles transport timeout and 500 error gracefully without crashing application', function () {
    Http::fake([
        'https://absensi-sandbox.sabira.id/api/v1/health-dispositions' => Http::response('Internal Server Error', 500),
    ]);

    $client = new HttpAttendanceSandboxIntegration;

    $dto = new AttendanceHealthDispositionDTO(
        eventId: 'EVT-DISP-500',
        eventVersion: 1,
        gateUserId: 'GATE-SAN-001',
        dispositionType: 'excused_health',
        effectiveFrom: Carbon::parse('2026-08-10'),
        effectiveUntil: Carbon::parse('2026-08-11'),
        activityScope: 'all_activities',
        sourceVisitReference: 'VISIT-20260810-001',
        issuedAt: Carbon::now()
    );

    $result = $client->publishDisposition($dto);

    expect($result['success'])->toBeFalse();
    expect($result['status_code'])->toBe(500);
    expect($result['error'])->toContain('HTTP 500');
});
