<?php

namespace App\DTOs\Integration;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Immutable DTO representing a health attendance disposition for SABIRA Absensi.
 */
class AttendanceHealthDispositionDTO
{
    /**
     * Forbidden clinical keys that must NEVER be present in the disposition.
     *
     * @var list<string>
     */
    public const FORBIDDEN_KEYS = [
        'diagnosis',
        'working_diagnosis',
        'icd10',
        'clinical_summary',
        'assessment_summary',
        'examination_findings',
        'history_current_illness',
        'medications',
        'allergies',
        'vital_signs',
        'internal_notes',
    ];

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $eventId,
        public readonly int $eventVersion,
        public readonly string $gateUserId,
        public readonly string $dispositionType, // excused_health, limited_activity, rest, return_to_activity, follow_up_external
        public readonly Carbon $effectiveFrom,
        public readonly ?Carbon $effectiveUntil,
        public readonly string $activityScope, // all_activities, academic_only, sports_only, boarding_only
        public readonly string $sourceVisitReference,
        public readonly Carbon $issuedAt,
        public readonly ?string $supersedesEventId = null,
        public readonly ?string $correlationId = null,
        public readonly string $sourceSystem = 'poskestren_health',
        public readonly array $metadata = []
    ) {
        $this->validateForbiddenKeys($metadata);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            eventVersion: (int) ($data['event_version'] ?? 1),
            gateUserId: $data['gate_user_id'],
            dispositionType: $data['disposition_type'],
            effectiveFrom: $data['effective_from'] instanceof Carbon ? $data['effective_from'] : Carbon::parse($data['effective_from']),
            effectiveUntil: ! empty($data['effective_until']) ? ($data['effective_until'] instanceof Carbon ? $data['effective_until'] : Carbon::parse($data['effective_until'])) : null,
            activityScope: $data['activity_scope'] ?? 'all_activities',
            sourceVisitReference: $data['source_visit_reference'],
            issuedAt: $data['issued_at'] instanceof Carbon ? $data['issued_at'] : Carbon::parse($data['issued_at']),
            supersedesEventId: $data['supersedes_event_id'] ?? null,
            correlationId: $data['correlation_id'] ?? null,
            sourceSystem: $data['source_system'] ?? 'poskestren_health',
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Convert DTO to JSON-serializable array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_version' => $this->eventVersion,
            'gate_user_id' => $this->gateUserId,
            'disposition_type' => $this->dispositionType,
            'effective_from' => $this->effectiveFrom->toIso8601String(),
            'effective_until' => $this->effectiveUntil?->toIso8601String(),
            'activity_scope' => $this->activityScope,
            'source_system' => $this->sourceSystem,
            'source_visit_reference' => $this->sourceVisitReference,
            'issued_at' => $this->issuedAt->toIso8601String(),
            'supersedes_event_id' => $this->supersedesEventId,
            'correlation_id' => $this->correlationId,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function validateForbiddenKeys(array $metadata): void
    {
        foreach (self::FORBIDDEN_KEYS as $forbidden) {
            if (array_key_exists($forbidden, $metadata)) {
                throw new InvalidArgumentException("Privacy violation: forbidden clinical key '{$forbidden}' found in attendance disposition payload.");
            }
        }
    }
}
