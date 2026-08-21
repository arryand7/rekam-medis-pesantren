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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GateSyncApplyService
{
    /** Disk untuk menyimpan foto profil person (private, tidak dapat diakses langsung) */
    private const PHOTO_DISK = 'person_photos';

    /** Direktori penyimpanan foto relatif terhadap disk (kosong karena disk sudah punya root) */
    private const PHOTO_DIR = '';

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
            'photos_synced' => 0,
            'photos_skipped' => 0,
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

                if ($result['photo_synced'] ?? false) {
                    $summary['photos_synced']++;
                } else {
                    $summary['photos_skipped']++;
                }

                $appliedItems[] = [
                    'gate_user_id' => $dto->gateUserId,
                    'name' => $dto->name,
                    'status' => $status,
                    'person_id' => $result['person_id'],
                    'message' => $result['message'],
                    'photo_synced' => $result['photo_synced'] ?? false,
                ];
            } catch (Throwable $e) {
                $summary['failed']++;
                $appliedItems[] = [
                    'gate_user_id' => $dto->gateUserId,
                    'name' => $dto->name,
                    'status' => 'failed',
                    'person_id' => null,
                    'message' => $e->getMessage(),
                    'photo_synced' => false,
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
     * @return array{status: string, person_id: ?string, message: string, photo_synced: bool}
     */
    public function applySingleRecord(GateUserDTO $dto, ?User $actor = null): array
    {
        if (empty($dto->gateUserId) || empty($dto->name)) {
            return [
                'status' => 'failed',
                'person_id' => null,
                'message' => 'Payload Gate tidak memiliki gate_user_id atau nama yang valid.',
                'photo_synced' => false,
            ];
        }

        $result = DB::transaction(function () use ($dto) {
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
                        'photo_synced' => false,
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

            // 4. Check if Unchanged (identity fields only — foto dicek terpisah)
            $identityUnchanged = ! $isNew
                && $person->checksum !== null
                && $person->checksum === $dto->checksum
                && $dto->sourceStatus === $person->source_status;

            if ($identityUnchanged) {
                // Meski identity unchanged, foto mungkin tetap perlu di-update
                return [
                    'status' => 'unchanged',
                    'person_id' => $person->id,
                    'message' => 'Identitas lokal identik dengan data Gate.',
                    'photo_synced' => false,
                    '_person' => $person,
                    '_dto' => $dto,
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
                'photo_synced' => false,
                '_person' => $person,
                '_dto' => $dto,
            ];
        });

        // 8. Download & sync foto DI LUAR TRANSAKSI (I/O tidak boleh di dalam transaksi DB)
        if (isset($result['_person'], $result['_dto'])) {
            $photoSynced = $this->syncPhoto($result['_person'], $result['_dto']);
            unset($result['_person'], $result['_dto']);
            $result['photo_synced'] = $photoSynced;
        }

        return $result;
    }

    /**
     * Unduh dan simpan foto profil dari Gate SSO ke local storage.
     * Hanya download jika checksum berbeda dengan yang sudah tersimpan.
     * Tidak di-wrap dalam DB transaction karena melibatkan I/O file.
     */
    protected function syncPhoto(Person $person, GateUserDTO $dto): bool
    {
        if (! $dto->photoAvailable || ! $dto->photoUrl) {
            return false;
        }

        // Skip jika checksum sama (foto tidak berubah)
        if ($dto->photoChecksum && $person->photo_checksum === $dto->photoChecksum) {
            return false;
        }

        try {
            $content = $this->gateClient->downloadPhoto($dto->photoUrl);

            if ($content === null || strlen($content) < 100) {
                Log::warning('Gate syncPhoto: download gagal atau konten kosong', [
                    'gate_user_id' => $dto->gateUserId,
                ]);

                return false;
            }

            // Deteksi ekstensi dari magic bytes
            $ext = $this->detectImageExtension($content);
            if ($ext === null) {
                Log::warning('Gate syncPhoto: format gambar tidak dikenal', [
                    'gate_user_id' => $dto->gateUserId,
                ]);

                return false;
            }

            // Hapus foto lama jika ada
            if ($person->photo_path && Storage::disk(self::PHOTO_DISK)->exists($person->photo_path)) {
                Storage::disk(self::PHOTO_DISK)->delete($person->photo_path);
            }

            // Simpan dengan nama acak untuk mencegah enumeration
            $filename = Str::uuid().'.'.$ext;
            Storage::disk(self::PHOTO_DISK)->put($filename, $content);

            // Update person record (standalone update, tidak perlu full transaction)
            $person->photo_path = $filename;
            $person->photo_checksum = $dto->photoChecksum ?? hash('sha256', $content);
            $person->save();

            return true;
        } catch (Throwable $e) {
            Log::error('Gate syncPhoto exception', [
                'gate_user_id' => $dto->gateUserId,
                'exception_class' => $e::class,
            ]);

            return false;
        }
    }

    /**
     * Deteksi ekstensi gambar dari magic bytes (header file).
     */
    private function detectImageExtension(string $content): ?string
    {
        $header = substr($content, 0, 12);

        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($header, "\x89PNG\r\n\x1A\n")) {
            return 'png';
        }
        if (str_starts_with($header, 'RIFF') && substr($content, 8, 4) === 'WEBP') {
            return 'webp';
        }
        if (str_starts_with($header, 'GIF87a') || str_starts_with($header, 'GIF89a')) {
            return 'gif';
        }

        return null;
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
