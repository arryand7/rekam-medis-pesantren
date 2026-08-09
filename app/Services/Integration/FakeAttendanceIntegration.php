<?php

namespace App\Services\Integration;

use App\Contracts\Integration\AttendanceIntegrationContract;
use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use Illuminate\Support\Str;

/**
 * In-memory mock/sandbox implementation of AttendanceIntegrationContract.
 */
class FakeAttendanceIntegration implements AttendanceIntegrationContract
{
    /**
     * Store sent dispositions in memory for test assertions.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $publishedDispositions = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $supersededDispositions = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $revokedDispositions = [];

    /**
     * Reset in-memory state.
     */
    public static function reset(): void
    {
        self::$publishedDispositions = [];
        self::$supersededDispositions = [];
        self::$revokedDispositions = [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getPublishedDispositions(): array
    {
        return self::$publishedDispositions;
    }

    public function publishDisposition(AttendanceHealthDispositionDTO $dto): array
    {
        $externalRef = 'ABS-'.Str::upper(Str::random(10));
        self::$publishedDispositions[$dto->eventId] = [
            'dto' => $dto->toArray(),
            'external_reference' => $externalRef,
            'received_at' => now()->toIso8601String(),
        ];

        return [
            'success' => true,
            'external_reference' => $externalRef,
            'status_code' => 200,
            'error' => null,
        ];
    }

    public function supersedeDisposition(string $originalEventId, AttendanceHealthDispositionDTO $newDto): array
    {
        $externalRef = 'ABS-SUP-'.Str::upper(Str::random(10));
        self::$supersededDispositions[$newDto->eventId] = [
            'original_event_id' => $originalEventId,
            'new_dto' => $newDto->toArray(),
            'external_reference' => $externalRef,
            'received_at' => now()->toIso8601String(),
        ];

        return [
            'success' => true,
            'external_reference' => $externalRef,
            'status_code' => 200,
            'error' => null,
        ];
    }

    public function revokeDisposition(string $eventId, string $reason): array
    {
        self::$revokedDispositions[$eventId] = [
            'reason' => $reason,
            'revoked_at' => now()->toIso8601String(),
        ];

        return [
            'success' => true,
            'external_reference' => 'ABS-REV-'.$eventId,
            'status_code' => 200,
            'error' => null,
        ];
    }

    public function probeHealth(): array
    {
        return [
            'driver' => 'fake',
            'enabled' => config('integration.attendance.enabled', false),
            'reachable' => true,
            'message' => 'Fake attendance integration sandbox is online and ready.',
        ];
    }
}
