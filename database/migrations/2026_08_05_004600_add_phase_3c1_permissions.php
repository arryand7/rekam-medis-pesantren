<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'view-visit-discharges',
                'display_name' => 'Lihat Kepulangan Kunjungan',
                'description' => 'Melihat status dan riwayat kepulangan/discharge kunjungan medis santri',
            ],
            [
                'name' => 'prepare-visit-discharges',
                'display_name' => 'Siapkan Draf Kepulangan',
                'description' => 'Menyusun draf rencana kepulangan, rekomendasi aktivitas, dan tindak lanjut',
            ],
            [
                'name' => 'finalize-visit-discharges',
                'display_name' => 'Finalisasi Kepulangan Kunjungan',
                'description' => 'Mengesahkan kepulangan klinis dan menutup kunjungan medis secara resmi',
            ],
            [
                'name' => 'amend-visit-discharges',
                'display_name' => 'Amandemen Kepulangan Kunjungan',
                'description' => 'Melakukan koreksi atau amandemen terkontrol terhadap kepulangan yang sudah difinalisasi',
            ],
            [
                'name' => 'manage-follow-up-plans',
                'display_name' => 'Kelola Rencana Tindak Lanjut',
                'description' => 'Membuat, menyelesaikan, atau membatalkan rencana kontrol/follow-up',
            ],
            [
                'name' => 'manage-activity-restrictions',
                'display_name' => 'Kelola Pembatasan Aktivitas',
                'description' => 'Menerbitkan dan mengubah surat/rekomendasi istirahat dan pembatasan aktivitas',
            ],
            [
                'name' => 'prepare-operational-handoffs',
                'display_name' => 'Siapkan Handoff Operasional',
                'description' => 'Menyusun serah terima instruksi perawatan internal ke pembina asrama/guru',
            ],
            [
                'name' => 'acknowledge-operational-handoffs',
                'display_name' => 'Konfirmasi Penerimaan Handoff',
                'description' => 'Mengonfirmasi penerimaan dan pemahaman instruksi handoff operasional',
            ],
            [
                'name' => 'download-discharge-summaries',
                'display_name' => 'Unduh Ringkasan Kepulangan',
                'description' => 'Mengunduh berkas ringkasan kepulangan privat terautentikasi',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'description' => $perm['description'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $names = [
            'view-visit-discharges',
            'prepare-visit-discharges',
            'finalize-visit-discharges',
            'amend-visit-discharges',
            'manage-follow-up-plans',
            'manage-activity-restrictions',
            'prepare-operational-handoffs',
            'acknowledge-operational-handoffs',
            'download-discharge-summaries',
        ];

        Permission::whereIn('name', $names)->delete();
    }
};
