<?php

namespace App\Services\Gate;

use App\Contracts\GateClientContract;
use App\DTOs\GateUserDTO;
use App\Models\GateSyncRun;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class GateSyncApplyService
{
    public function __construct(
        protected GateClientContract $gateClient,
        protected GateSyncDryRunService $dryRunService
    ) {}

    /**
     * Perform a transactional, idempotent Apply Sync of Gate users into local identity projections.
     *
     * @return array{
     *   run_id: string,
     *   summary: array<string, int>,
     *   items: array<int, array<string, mixed>>,
     *   status: string
     * }
     */
    public function executeApply(int $page = 1, int $perPage = 50, ?User $actor = null): array
    {
        $actor = $actor ?? Auth::user();

        $syncRun = GateSyncRun::create([
            'run_type' => 'apply',
            'status' => 'running',
            'total_records' => 0,
            'applied_count' => 0,
            'failed_count' => 0,
            'conflict_count' => 0,
            'summary_json' => [],
            'executed_by_id' => $actor?->id,
            'started_at' => now(),
        ]);

        AuditLogService::log(
            action: 'gate_sync.apply_started',
            subjectType: 'GateSyncRun',
            subjectId: $syncRun->id,
            before: null,
            after: ['page' => $page, 'per_page' => $perPage],
            reason: 'Sinkronisasi identitas Gate (Apply Mode) dimulai oleh '.($actor ? $actor->name : 'Sistem')
        );

        $payload = $this->gateClient->fetchUsers($page, $perPage);
        /** @var GateUserDTO[] $dtos */
        $dtos = $payload['data'];

        $summary = [
            'total' => count($dtos),
            'applied_new' => 0,
            'applied_changed' => 0,
            'applied_deactivated' => 0,
            'unchanged' => 0,
            'conflicts' => 0,
            'failed' => 0,
        ];

        $appliedItems = [];

        foreach ($dtos as $dto) {
            try {
                $result = $this->applySingleRecord($dto, $actor);
                $status = $result['status'];

                if ($status === 'new') {
                    $summary['applied_new']++;
                } elseif ($status === 'changed') {
                    $summary['applied_changed']++;
                } elseif ($status === 'deactivated') {
                    $summary['applied_deactivated']++;
                } elseif ($status === 'unchanged') {
                    $summary['unchanged']++;
                } elseif ($status === 'conflict') {
                    $summary['conflicts']++;
                }

                $appliedItems[] = [
                    'gate_user_id' => $dto->gateUserId,
                    'name' => $dto->name,
                    'status' => $status,
                    'person_id' => $result['person_id'],
                    'message' => $result['message'],
                ];
            } catch (Throwable $e) {
                $summary['failed']++;
                $appliedItems[] = [
                    'gate_user_id' => $dto->gateUserId,
                    'name' => $dto->name,
                    'status' => 'failed',
                    'person_id' => null,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $syncRun->update([
            'status' => $summary['failed'] > 0 ? 'partial' : 'completed',
            'total_records' => $summary['total'],
            'applied_count' => $summary['applied_new'] + $summary['applied_changed'] + $summary['applied_deactivated'],
            'failed_count' => $summary['failed'],
            'conflict_count' => $summary['conflicts'],
            'summary_json' => $summary,
            'completed_at' => now(),
        ]);

        AuditLogService::log(
            action: 'gate_sync.completed',
            subjectType: 'GateSyncRun',
            subjectId: $syncRun->id,
            before: null,
            after: $summary,
            reason: 'Sinkronisasi identitas Gate (Apply Mode) selesai'
        );

        return [
            'run_id' => $syncRun->id,
            'summary' => $summary,
            'items' => $appliedItems,
            'status' => $syncRun->status,
        ];
    }

    /**
     * Atomically apply an individual Gate user DTO.
     *
     * @return array{status: string, person_id: ?string, message: string}
     */
    public function applySingleRecord(GateUserDTO $dto, ?User $actor = null): array
    {
        if (empty($dto->gateUserId) || empty($dto->name)) {
            return [
                'status' => 'failed',
                'person_id' => null,
                'message' => 'Payload Gate tidak memiliki gate_user_id atau nama yang valid.',
            ];
        }

        return DB::transaction(function () use ($dto) {
            // 1. Lock Person record by gate_user_id
            $person = Person::where('gate_user_id', $dto->gateUserId)->lockForUpdate()->first();

            // Check approved mappings if not found by gate_user_id
            if (! $person) {
                $mapping = DB::table('gate_identity_mappings')
                    ->where('gate_user_id', $dto->gateUserId)
                    ->where('status', 'approved')
                    ->first();

                if ($mapping) {
                    $person = Person::where('id', $mapping->person_id)->lockForUpdate()->first();
                }
            }

            // 2. Check for Potential Conflict if Person not matched yet
            if (! $person) {
                $potentialMatch = null;
                if (! empty($dto->nisNip)) {
                    $potentialMatch = Person::where('nis_nip', $dto->nisNip)->first();
                } elseif (! empty($dto->nik)) {
                    $potentialMatch = Person::where('nik', $dto->nik)->first();
                }

                if ($potentialMatch) {
                    // Create pending mapping for manual review
                    DB::table('gate_identity_mappings')->updateOrInsert(
                        ['gate_user_id' => $dto->gateUserId, 'person_id' => $potentialMatch->id],
                        [
                            'id' => (string) Str::ulid(),
                            'mapping_method' => ! empty($dto->nisNip) ? 'nis_match' : 'nik_match',
                            'confidence_score' => 0.85,
                            'status' => 'pending',
                            'notes' => "Konflik potensial ditemukan dengan {$potentialMatch->name} via NIK/NIS.",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    return [
                        'status' => 'conflict',
                        'person_id' => $potentialMatch->id,
                        'message' => "Kandidat konflik dengan person {$potentialMatch->id}. Ditandai untuk tinjauan manual.",
                    ];
                }
            }

            // 3. New Record
            $isNew = false;
            if (! $person) {
                $person = new Person;
                $person->gate_user_id = $dto->gateUserId;
                $isNew = true;
            }

            // 4. Check if Unchanged
            if (! $isNew && $person->checksum !== null && $person->checksum === $dto->checksum && $dto->sourceStatus === $person->source_status) {
                return [
                    'status' => 'unchanged',
                    'person_id' => $person->id,
                    'message' => 'Identitas lokal identik dengan data Gate.',
                ];
            }

            // 5. Update Authoritative Projection
            $person->name = $dto->name;
            if (! empty($dto->nik)) {
                $person->nik = $dto->nik;
            }
            if (! empty($dto->nisNip)) {
                $person->nis_nip = $dto->nisNip;
            }
            $person->user_type = $dto->userType;
            if (! empty($dto->gender)) {
                $person->gender = $this->normalizeGender($dto->gender);
            }
            $person->phone = $dto->phone;
            $person->email = $dto->email;
            $person->source_status = $dto->sourceStatus;
            $person->checksum = $dto->checksum;
            $person->source_version = $dto->sourceVersion;
            $person->source_updated_at = $dto->sourceUpdatedAt ? now()->parse($dto->sourceUpdatedAt) : now();
            $person->synced_at = now();
            $person->save();

            // 6. Project User
            $user = User::where('person_id', $person->id)->lockForUpdate()->first()
                ?? ($person->email ? User::where('email', $person->email)->lockForUpdate()->first() : null);

            if (! $user) {
                $user = new User;
                $user->person_id = $person->id;
                $user->password = bcrypt(Str::random(32));
            }

            $user->person_id = $person->id;
            $user->name = $person->name;
            $user->email = $person->email ?? "{$dto->gateUserId}@gate.example.invalid";
            $user->is_active = ($dto->sourceStatus === 'active');
            $user->save();

            // 7. Project Patient (If Human and Patient record does not exist yet)
            if ($person->isHumanPatientEligible()) {
                Patient::createOrFindForPerson($person);
            }

            $status = $isNew ? 'new' : ($dto->sourceStatus !== 'active' ? 'deactivated' : 'changed');

            AuditLogService::log(
                action: 'gate_sync.item_applied',
                subjectType: 'Person',
                subjectId: $person->id,
                before: null,
                after: ['status' => $status, 'gate_user_id' => $dto->gateUserId],
                reason: "Sinkronisasi apply berhasil untuk {$person->name} ({$status})"
            );

            return [
                'status' => $status,
                'person_id' => $person->id,
                'message' => "Proyeksi identitas berhasil diperbarui ({$status}).",
            ];
        });
    }

    /**
     * Normalize gender value to single character (L / P).
     */
    protected function normalizeGender(?string $gender): ?string
    {
        if (empty($gender)) {
            return null;
        }

        $g = strtolower(trim($gender));
        if ($g === 'l' || str_starts_with($g, 'laki') || $g === 'male' || $g === 'm') {
            return 'L';
        }
        if ($g === 'p' || str_starts_with($g, 'perempuan') || $g === 'female' || $g === 'f') {
            return 'P';
        }

        return substr(strtoupper($gender), 0, 1);
    }
}
