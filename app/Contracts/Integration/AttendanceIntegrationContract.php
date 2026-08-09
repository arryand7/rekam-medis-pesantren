<?php

namespace App\Contracts\Integration;

use App\DTOs\Integration\AttendanceHealthDispositionDTO;

/**
 * Contract for integrating health attendance dispositions with SABIRA Absensi.
 */
interface AttendanceIntegrationContract
{
    /**
     * Publish a health-related attendance disposition (excused, rest, limited, return-to-activity).
     *
     * @return array{success: bool, external_reference: ?string, status_code: ?int, error: ?string}
     */
    public function publishDisposition(AttendanceHealthDispositionDTO $dto): array;

    /**
     * Supersede an existing attendance disposition with a newly amended disposition.
     *
     * @return array{success: bool, external_reference: ?string, status_code: ?int, error: ?string}
     */
    public function supersedeDisposition(string $originalEventId, AttendanceHealthDispositionDTO $newDto): array;

    /**
     * Revoke or cancel an attendance disposition.
     *
     * @return array{success: bool, external_reference: ?string, status_code: ?int, error: ?string}
     */
    public function revokeDisposition(string $eventId, string $reason): array;

    /**
     * Check integration driver status and health.
     *
     * @return array{driver: string, enabled: bool, reachable: bool, message: string}
     */
    public function probeHealth(): array;
}
