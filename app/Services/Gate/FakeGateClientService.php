<?php

namespace App\Services\Gate;

use App\Contracts\GateClientContract;
use App\DTOs\GateUserDTO;

class FakeGateClientService implements GateClientContract
{
    /** @var array<string, array> */
    protected array $mockUsers = [];

    public function __construct()
    {
        $this->seedSyntheticUsers();
    }

    protected function seedSyntheticUsers(): void
    {
        $samples = [
            [
                'gate_user_id' => 'GATE-SAN-001',
                'name' => 'Ahmad Santri Syaraf',
                'nik' => '3201010010001',
                'nis_nip' => 'SAN-2026-001',
                'user_type' => 'santri',
                'gender' => 'L',
                'phone' => '081234567890',
                'email' => 'ahmad.santri@sabira.test',
                'source_status' => 'active',
                'source_updated_at' => '2026-08-01 10:00:00',
                'source_version' => 'v1.0',
                'checksum' => 'hash_san_001',
            ],
            [
                'gate_user_id' => 'GATE-UST-002',
                'name' => 'Ustadz Abdullah Pengasuh',
                'nik' => '3201010010002',
                'nis_nip' => 'UST-2026-002',
                'user_type' => 'pengasuh',
                'gender' => 'L',
                'phone' => '081234567891',
                'email' => 'abdullah.pengasuh@sabira.test',
                'source_status' => 'active',
                'source_updated_at' => '2026-08-01 10:00:00',
                'source_version' => 'v1.0',
                'checksum' => 'hash_ust_002',
            ],
            [
                'gate_user_id' => 'GATE-MED-003',
                'name' => 'dr. Fatimah Medis',
                'nik' => '3201010010003',
                'nis_nip' => 'MED-2026-003',
                'user_type' => 'petugas_kesehatan',
                'gender' => 'P',
                'phone' => '081234567892',
                'email' => 'fatimah.medis@sabira.test',
                'source_status' => 'active',
                'source_updated_at' => '2026-08-01 10:00:00',
                'source_version' => 'v1.0',
                'checksum' => 'hash_med_003',
            ],
            [
                'gate_user_id' => 'GATE-ADM-004',
                'name' => 'Admin Utama Poskestren',
                'nik' => '3201010010004',
                'nis_nip' => 'ADM-2026-004',
                'user_type' => 'admin',
                'gender' => 'L',
                'phone' => '081234567893',
                'email' => 'admin.poskestren@sabira.test',
                'source_status' => 'active',
                'source_updated_at' => '2026-08-01 10:00:00',
                'source_version' => 'v1.0',
                'checksum' => 'hash_adm_004',
            ],
            [
                'gate_user_id' => 'GATE-BOT-005',
                'name' => 'Gate Notification Bot Service',
                'nik' => null,
                'nis_nip' => 'BOT-001',
                'user_type' => 'service_account',
                'gender' => null,
                'phone' => null,
                'email' => 'bot.service@sabira.test',
                'source_status' => 'active',
                'source_updated_at' => '2026-08-01 10:00:00',
                'source_version' => 'v1.0',
                'checksum' => 'hash_bot_005',
            ],
        ];

        foreach ($samples as $sample) {
            $this->mockUsers[$sample['gate_user_id']] = $sample;
        }
    }

    public function fetchUsers(int $page = 1, int $perPage = 50): array
    {
        $all = array_values($this->mockUsers);
        $totalItems = count($all);
        $totalPages = (int) ceil($totalItems / $perPage);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($all, $offset, $perPage);

        $dtos = array_map(fn ($item) => GateUserDTO::fromArray($item), $sliced);

        return [
            'data' => $dtos,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
        ];
    }

    public function fetchUserById(string $gateUserId): ?GateUserDTO
    {
        if (isset($this->mockUsers[$gateUserId])) {
            return GateUserDTO::fromArray($this->mockUsers[$gateUserId]);
        }

        return null;
    }

    public function ping(): bool
    {
        return true;
    }
}
