<?php

namespace App\Services;

use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\OperationalNotification;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\Integration\AttendanceDispositionPayloadBuilder;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service managing operational notification domain (Dorm, Homeroom, Guardian, Staff).
 */
class OperationalNotificationService
{
    public function __construct(
        protected AttendanceDispositionPayloadBuilder $payloadBuilder = new AttendanceDispositionPayloadBuilder,
        protected IntegrationOutboxService $outboxService = new IntegrationOutboxService
    ) {}

    /**
     * Prepare a single operational notification.
     *
     * @param  array<string, mixed>  $data
     */
    public function prepareNotification(array $data, ?User $actor = null): OperationalNotification
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            $notification = OperationalNotification::create([
                'person_id' => $data['person_id'],
                'patient_id' => $data['patient_id'] ?? null,
                'medical_visit_id' => $data['medical_visit_id'] ?? null,
                'visit_discharge_id' => $data['visit_discharge_id'] ?? null,
                'activity_restriction_id' => $data['activity_restriction_id'] ?? null,
                'notification_type' => $data['notification_type'],
                'recipient_type' => $data['recipient_type'],
                'recipient_reference' => $data['recipient_reference'] ?? null,
                'payload_snapshot' => $data['payload_snapshot'],
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'prepared',
                'prepared_by_id' => $actor?->id,
                'prepared_at' => now(),
                'ready_at' => now(),
                'correlation_id' => $data['correlation_id'] ?? (string) Str::ulid(),
            ]);

            AuditLogService::log(
                action: 'operational_notification.prepared',
                subjectType: 'OperationalNotification',
                subjectId: $notification->id,
                before: null,
                after: $notification->toArray(),
                reason: "Notifikasi operasional {$notification->notification_type} untuk {$notification->recipient_type} disiapkan"
            );

            return $notification;
        });
    }

    /**
     * Acknowledge an operational notification.
     */
    public function acknowledgeNotification(OperationalNotification $notification, ?string $notes = null, ?User $actor = null): OperationalNotification
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($notification, $notes, $actor) {
            /** @var OperationalNotification $locked */
            $locked = OperationalNotification::where('id', $notification->id)->lockForUpdate()->firstOrFail();

            if ($locked->isCancelled()) {
                throw new Exception('Tidak dapat mengonfirmasi notifikasi yang telah dibatalkan.');
            }

            $locked->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'acknowledged_by_id' => $actor?->id,
                'acknowledgement_notes' => $notes !== null ? trim($notes) : null,
            ]);

            AuditLogService::log(
                action: 'operational_notification.acknowledged',
                subjectType: 'OperationalNotification',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: 'Notifikasi operasional dikonfirmasi penerimaannya oleh '.($actor !== null ? $actor->name : 'Sistem')
            );

            return $locked;
        });
    }

    /**
     * Cancel an operational notification.
     */
    public function cancelNotification(OperationalNotification $notification, string $reason, ?User $actor = null): OperationalNotification
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($notification, $reason) {
            /** @var OperationalNotification $locked */
            $locked = OperationalNotification::where('id', $notification->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => trim($reason),
            ]);

            AuditLogService::log(
                action: 'operational_notification.cancelled',
                subjectType: 'OperationalNotification',
                subjectId: $locked->id,
                before: null,
                after: $locked->toArray(),
                reason: "Notifikasi operasional dibatalkan: {$reason}"
            );

            return $locked;
        });
    }

    /**
     * Automatically dispatch minimum-necessary operational notifications & outbox event for a finalized discharge.
     *
     * @return array<string, mixed>
     */
    public function dispatchDischargeNotifications(VisitDischarge $discharge, ?User $actor = null): array
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($discharge, $actor) {
            $visit = $discharge->medicalVisit;
            if (! $visit instanceof MedicalVisit) {
                throw new \RuntimeException("MedicalVisit for discharge {$discharge->id} not found.");
            }

            $patient = $visit->patient;
            if (! $patient instanceof Patient) {
                throw new \RuntimeException("Patient for visit {$visit->id} not found.");
            }

            $person = $patient->person;
            if (! $person instanceof Person) {
                throw new \RuntimeException("Person for patient {$patient->id} not found.");
            }

            $rawRestriction = $discharge->activityRestrictions()->where('status', 'active')->latest()->first();
            $restriction = $rawRestriction instanceof ActivityRestriction ? $rawRestriction : null;

            $correlationId = (string) Str::ulid();
            $createdNotifications = [];

            // 1. Dorm Supervisor Notification
            $dormPayload = $this->payloadBuilder->buildDormSupervisorPayload($person, $discharge, $restriction);
            $createdNotifications[] = $this->prepareNotification([
                'person_id' => $person->id,
                'patient_id' => $visit->patient_id,
                'medical_visit_id' => $visit->id,
                'visit_discharge_id' => $discharge->id,
                'activity_restriction_id' => $restriction?->id,
                'notification_type' => 'health_visit_closed',
                'recipient_type' => 'dorm_supervisor',
                'payload_snapshot' => $dormPayload,
                'priority' => 'normal',
                'correlation_id' => $correlationId,
            ], $actor);

            // 2. Homeroom Teacher Notification (if student / restriction exists)
            if ($restriction !== null || $discharge->activity_recommendation !== 'full_activity') {
                $homeroomPayload = $this->payloadBuilder->buildHomeroomTeacherPayload($person, $discharge, $restriction);
                $createdNotifications[] = $this->prepareNotification([
                    'person_id' => $person->id,
                    'patient_id' => $visit->patient_id,
                    'medical_visit_id' => $visit->id,
                    'visit_discharge_id' => $discharge->id,
                    'activity_restriction_id' => $restriction?->id,
                    'notification_type' => 'limited_activity',
                    'recipient_type' => 'homeroom_teacher',
                    'payload_snapshot' => $homeroomPayload,
                    'priority' => 'normal',
                    'correlation_id' => $correlationId,
                ], $actor);
            }

            // 3. Attendance Outbox Event (if Gate User ID exists, otherwise create Identity Conflict)
            $outboxEvent = null;
            if (! empty($person->gate_user_id)) {
                $eventId = (string) Str::ulid();
                $dto = $this->payloadBuilder->buildAttendanceDTO(
                    eventId: $eventId,
                    person: $person,
                    visit: $visit,
                    discharge: $discharge,
                    restriction: $restriction,
                    correlationId: $correlationId
                );

                $idempotencyKey = 'ABS-DISCHARGE-'.$discharge->id.'-V'.$discharge->lock_version;

                $outboxEvent = $this->outboxService->createOutboxEvent(
                    eventType: 'health_disposition_published',
                    aggregateType: 'VisitDischarge',
                    aggregateId: $discharge->id,
                    destination: 'attendance_system',
                    payload: $dto->toArray(),
                    idempotencyKey: $idempotencyKey,
                    correlationId: $correlationId,
                    actor: $actor
                );
            } else {
                $this->outboxService->recordIdentityConflict(
                    person: $person,
                    destination: 'attendance_system',
                    conflictType: 'missing_gate_user_id',
                    snapshot: [
                        'visit_id' => $visit->id,
                        'discharge_id' => $discharge->id,
                        'reason' => 'Person does not have an authoritative Gate User ID.',
                    ],
                    correlationId: $correlationId
                );
            }

            return [
                'notifications' => $createdNotifications,
                'outbox_event' => $outboxEvent,
                'correlation_id' => $correlationId,
            ];
        });
    }
}
