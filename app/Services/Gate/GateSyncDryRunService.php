<?php

namespace App\Services\Gate;

use App\Contracts\GateClientContract;
use App\DTOs\GateUserDTO;
use App\Models\Person;
use App\Services\AuditLogService;

class GateSyncDryRunService
{
    public function __construct(
        protected GateClientContract $gateClient
    ) {}

    /**
     * Perform a non-mutating Dry-Run sync preview.
     *
     * @return array{
     *   summary: array<string, int>,
     *   items: array<int, array{
     *     gate_user_id: string,
     *     name: string,
     *     user_type: string,
     *     classification: string,
     *     matched_person_id: ?string,
     *     reason: string
     *   }>,
     *   executed_at: string
     * }
     */
    public function execute(int $page = 1, int $perPage = 50): array
    {
        $payload = $this->gateClient->fetchUsers($page, $perPage);
        /** @var GateUserDTO[] $dtos */
        $dtos = $payload['data'];

        $summary = [
            'total' => count($dtos),
            'new' => 0,
            'matched' => 0,
            'changed' => 0,
            'deactivated' => 0,
            'source_missing' => 0,
            'conflict' => 0,
            'unsupported_type' => 0,
            'duplicate_identifier' => 0,
            'invalid_payload' => 0,
            'unchanged' => 0,
        ];

        $items = [];

        foreach ($dtos as $dto) {
            $classification = $this->classifyRecord($dto);
            $summary[$classification['status']]++;

            $items[] = [
                'gate_user_id' => $dto->gateUserId,
                'name' => $dto->name,
                'user_type' => $dto->userType,
                'classification' => $classification['status'],
                'matched_person_id' => $classification['matched_person_id'],
                'reason' => $classification['reason'],
            ];
        }

        $report = [
            'summary' => $summary,
            'items' => $items,
            'executed_at' => now()->toIso8601String(),
        ];

        // Audit the dry-run execution
        AuditLogService::log(
            action: 'gate_sync.dry_run',
            subjectType: 'GateSyncPreview',
            subjectId: null,
            before: null,
            after: $summary,
            reason: 'Simulasi dry-run sinkronisasi pengguna Gate'
        );

        return $report;
    }

    /**
     * Classify an individual Gate user DTO against local database state.
     *
     * @return array{status: string, matched_person_id: ?string, reason: string}
     */
    protected function classifyRecord(GateUserDTO $dto): array
    {
        // 1. Schema / payload validity check
        if (empty($dto->gateUserId) || empty($dto->name)) {
            return [
                'status' => 'invalid_payload',
                'matched_person_id' => null,
                'reason' => 'Payload Gate tidak memiliki gate_user_id atau nama yang valid.',
            ];
        }

        // 2. Primary match by exact gate_user_id
        $personByGateId = Person::where('gate_user_id', $dto->gateUserId)->first();

        if ($personByGateId) {
            if ($dto->sourceStatus === 'inactive' || $dto->sourceStatus === 'deactivated') {
                return [
                    'status' => 'deactivated',
                    'matched_person_id' => $personByGateId->id,
                    'reason' => 'Pengguna dinonaktifkan di sistem Gate.',
                ];
            }

            if ($personByGateId->checksum !== null && $personByGateId->checksum === $dto->checksum) {
                return [
                    'status' => 'unchanged',
                    'matched_person_id' => $personByGateId->id,
                    'reason' => 'Data identitas lokal dan Gate identik.',
                ];
            }

            return [
                'status' => 'changed',
                'matched_person_id' => $personByGateId->id,
                'reason' => 'Perubahan atribut terdeteksi dari sistem Gate.',
            ];
        }

        // 3. Check potential candidate match by NIS/NIP or NIK (Requires manual review to prevent duplicate identity)
        $potentialMatch = null;
        if (! empty($dto->nisNip)) {
            $potentialMatch = Person::where('nis_nip', $dto->nisNip)->first();
        } elseif (! empty($dto->nik)) {
            $potentialMatch = Person::where('nik', $dto->nik)->first();
        }

        if ($potentialMatch) {
            return [
                'status' => 'conflict',
                'matched_person_id' => $potentialMatch->id,
                'reason' => "Kandidat konflik identitas terdeteksi pada person {$potentialMatch->id} via NIK/NIS/NIP. Butuh tinjauan manual.",
            ];
        }

        // 4. Default: New record candidate
        return [
            'status' => 'new',
            'matched_person_id' => null,
            'reason' => 'Identitas baru dari Gate yang belum terdaftar di POSKESTREN.',
        ];
    }
}
