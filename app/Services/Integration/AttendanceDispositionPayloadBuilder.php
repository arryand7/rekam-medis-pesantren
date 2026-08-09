<?php

namespace App\Services\Integration;

use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\Person;
use App\Models\VisitDischarge;
use InvalidArgumentException;

/**
 * Builds minimum-necessary operational payloads adhering to privacy profiles.
 */
class AttendanceDispositionPayloadBuilder
{
    /**
     * Forbidden clinical keys that must NEVER be leaked to any operational payload.
     *
     * @var list<string>
     */
    public const FORBIDDEN_CLINICAL_KEYS = [
        'diagnosis',
        'working_diagnosis',
        'icd10',
        'clinical_summary',
        'assessment_summary',
        'examination_findings',
        'history_current_illness',
        'medications',
        'medicine_orders',
        'allergies',
        'vital_signs',
        'internal_notes',
        'consultation_advice',
    ];

    /**
     * Build privacy-safe payload for Dormitory Supervisor (Pembina Asrama).
     *
     * @return array<string, mixed>
     */
    public function buildDormSupervisorPayload(
        Person $person,
        VisitDischarge $discharge,
        ?ActivityRestriction $restriction = null,
        ?string $emergencyEscalation = null
    ): array {
        $effectiveStart = $restriction !== null ? $restriction->effective_start : now();
        $effectiveUntil = $restriction !== null ? $restriction->effective_until : null;

        $payload = [
            'recipient_type' => 'dorm_supervisor',
            'person_identifier' => $person->gate_user_id ?? $person->id,
            'person_name' => $person->full_name,
            'rest_recommendation' => $discharge->rest_recommendation ?? 'Tidak ada anjuran istirahat khusus',
            'activity_recommendation' => $discharge->activity_recommendation,
            'restriction_type' => $restriction !== null ? $restriction->restriction_type : 'none',
            'effective_start' => $effectiveStart->toIso8601String(),
            'effective_until' => $effectiveUntil ? $effectiveUntil->toIso8601String() : null,
            'practical_instructions' => $discharge->restriction_notes ?? 'Aktivitas wajar sesuai anjuran',
            'follow_up_required' => $discharge->follow_up_required,
            'follow_up_date' => $discharge->follow_up_date?->toIso8601String(),
            'emergency_escalation' => $emergencyEscalation,
        ];

        $this->assertNoForbiddenKeys($payload);

        return $payload;
    }

    /**
     * Build privacy-safe payload for Homeroom Teacher (Wali Kelas / Guru).
     *
     * @return array<string, mixed>
     */
    public function buildHomeroomTeacherPayload(
        Person $person,
        VisitDischarge $discharge,
        ?ActivityRestriction $restriction = null
    ): array {
        $effectiveStart = $restriction !== null ? $restriction->effective_start : now();
        $effectiveUntil = $restriction !== null ? $restriction->effective_until : null;

        $payload = [
            'recipient_type' => 'homeroom_teacher',
            'person_identifier' => $person->gate_user_id ?? $person->id,
            'person_name' => $person->full_name,
            'school_activity_status' => $discharge->activity_recommendation,
            'effective_start' => $effectiveStart->toIso8601String(),
            'effective_until' => $effectiveUntil ? $effectiveUntil->toIso8601String() : null,
            'attendance_accommodation' => $discharge->restriction_notes ?? 'Mengikuti KBM normal/terbatas',
            'follow_up_date' => $discharge->follow_up_date?->toIso8601String(),
        ];

        $this->assertNoForbiddenKeys($payload);

        return $payload;
    }

    /**
     * Build AttendanceHealthDispositionDTO for SABIRA Absensi.
     */
    public function buildAttendanceDTO(
        string $eventId,
        Person $person,
        MedicalVisit $visit,
        VisitDischarge $discharge,
        ?ActivityRestriction $restriction = null,
        ?string $supersedesEventId = null,
        ?string $correlationId = null
    ): AttendanceHealthDispositionDTO {
        if (empty($person->gate_user_id)) {
            throw new InvalidArgumentException("Person {$person->id} does not have an authoritative Gate User ID.");
        }

        $dispositionType = match ($discharge->activity_recommendation) {
            'bed_rest' => 'rest',
            'limited_activity', 'light_activity' => 'limited_activity',
            'avoid_sports', 'no_strenuous_activity' => 'limited_activity',
            'return_to_activity', 'full_activity' => 'return_to_activity',
            default => 'excused_health',
        };

        $activityScope = 'all_activities';
        if ($restriction !== null) {
            $activityScope = match ($restriction->restriction_type) {
                'physical_education' => 'sports_only',
                'academic_classes' => 'academic_only',
                'dormitory_chores' => 'boarding_only',
                default => 'all_activities',
            };
        }

        $effectiveFrom = $restriction !== null ? $restriction->effective_start : now();
        $effectiveUntil = $restriction !== null ? $restriction->effective_until : null;

        return new AttendanceHealthDispositionDTO(
            eventId: $eventId,
            eventVersion: 1,
            gateUserId: $person->gate_user_id,
            dispositionType: $dispositionType,
            effectiveFrom: $effectiveFrom,
            effectiveUntil: $effectiveUntil,
            activityScope: $activityScope,
            sourceVisitReference: $visit->visit_number,
            issuedAt: now(),
            supersedesEventId: $supersedesEventId,
            correlationId: $correlationId,
            metadata: [
                'operational_reason_category' => 'health_care',
                'restriction_category' => $restriction !== null ? $restriction->restriction_type : 'none',
            ]
        );
    }

    /**
     * Ensure no forbidden clinical keys exist in array payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function assertNoForbiddenKeys(array $payload): void
    {
        foreach (self::FORBIDDEN_CLINICAL_KEYS as $forbidden) {
            if (array_key_exists($forbidden, $payload)) {
                throw new InvalidArgumentException("Privacy violation: forbidden key '{$forbidden}' present in operational payload.");
            }
        }
    }
}
