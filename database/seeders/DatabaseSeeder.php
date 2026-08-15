<?php

namespace Database\Seeders;

use App\Models\HealthcarePartner;
use App\Models\HealthcarePartnerContact;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientHealthProfile;
use App\Models\PatientMedicalCondition;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'manage-users' => 'Kelola Akun Pengguna',
            'manage-roles' => 'Kelola Role & Peran',
            'manage-permissions' => 'Kelola Hak Akses',
            'view-people' => 'Lihat Direktori Person',
            'view-patients' => 'Lihat Rekam Medis & Pasien',
            'manage-gate-sync' => 'Jalankan Preview & Sync Gate',
            'view-gate-sync' => 'Lihat Preview Sync Gate',
            'resolve-identity-conflicts' => 'Resolusi Konflik Identitas',
            'view-audit-log' => 'Lihat Log Audit Sistem',
            'manage-system-settings' => 'Kelola Pengaturan Sistem',

            // Phase 2A Permissions
            'view-patient-health-profile' => 'Lihat Profil Kesehatan Pasien',
            'update-patient-health-profile' => 'Perbarui Profil Kesehatan Pasien',
            'manage-patient-allergies' => 'Kelola Alergi Terstruktur',
            'manage-patient-conditions' => 'Kelola Kondisi Medis Penting',
            'manage-emergency-contacts' => 'Kelola Kontak Darurat',
            'create-medical-visits' => 'Registrasi Kunjungan Medis (Intake)',
            'view-medical-visits' => 'Lihat Antrean Kunjungan Medis',
            'cancel-medical-visits' => 'Batalkan Kunjungan Medis',
            'override-active-visit' => 'Override Kunjungan Aktif Pasien',

            // Phase 2B Permissions
            'record-vital-signs' => 'Pencatatan Tanda Vital',
            'finalize-vital-signs' => 'Finalisasi Tanda Vital',
            'create-clinical-assessments' => 'Pengkajian Klinis Medis',
            'finalize-clinical-assessments' => 'Finalisasi Pengkajian Klinis',
            'amend-clinical-assessments' => 'Addendum Pengkajian Klinis',
            'record-working-diagnosis' => 'Pencatatan Impresi Diagnostik',
            'record-initial-actions' => 'Tindakan Awal Non-Obat',
            'recommend-visit-disposition' => 'Penetapan Rekomendasi Disposisi',

            // Phase 2C Permissions
            'start-observations' => 'Memulai Observasi Poskestren',
            'view-observations' => 'Lihat Antrean & Monitoring Observasi',
            'record-observation-monitoring' => 'Catat Pemantauan Berkala Observasi',
            'finalize-observation-monitoring' => 'Finalisasi Pemantauan Berkala',
            'amend-observation-monitoring' => 'Addendum Lembar Pemantauan',
            'prepare-observation-handover' => 'Pengajuan Serah Terima Jaga (Handover)',
            'acknowledge-observation-handover' => 'Konfirmasi Handover & Alih Penanggung Jawab',
            'complete-observations' => 'Penyelesaian Episode Observasi',
            'cancel-observations' => 'Pembatalan Episode Observasi',
            'view-observation-audit' => 'Lihat Audit Trail Observasi',

            // Phase 2D1 Permissions
            'view-pharmacy-inventory' => 'Lihat Stok & Inventaris Farmasi',
            'manage-medicine-master' => 'Kelola Master Data Obat',
            'receive-medicine-stock' => 'Penerimaan Stok Obat Baru',
            'adjust-medicine-stock' => 'Penyesuaian Stok (Stock Opname)',
            'reverse-stock-movements' => 'Pembatalan Mutasi Stok (Reversal)',
            'transfer-medicine-stock' => 'Transfer Stok Antar-Lokasi',
            'view-stock-movements' => 'Lihat Riwayat Mutasi Stok',
            'view-stock-reconciliation' => 'Lihat Laporan Rekonsiliasi Stok',
            'manage-stock-locations' => 'Kelola Lokasi Penyimpanan Stok',

            // Phase 2D2 Permissions
            'view-medication-orders' => 'Lihat Instruksi Obat (Orders)',
            'create-medication-orders' => 'Buat Instruksi Obat Baru',
            'activate-medication-orders' => 'Aktivasi Instruksi Obat',
            'revise-medication-orders' => 'Revisi Instruksi Obat',
            'discontinue-medication-orders' => 'Penghentian Instruksi Obat',
            'view-medication-administrations' => 'Lihat Catatan Pemberian Obat',
            'schedule-medication-administrations' => 'Jadwalkan Pemberian Obat',
            'administer-medications' => 'Catat Pemberian Obat ke Pasien & Potong Stok',
            'administer-one-time-medication' => 'Pemberian Obat Sekali Jalan (One-Time)',
            'hold-medications' => 'Tunda Pemberian Obat (Hold)',
            'record-medication-refusal' => 'Catat Penolakan Obat Pasien',
            'record-missed-medication' => 'Catat Terlewat Pemberian Obat',
            'correct-medication-administrations' => 'Koreksi Pemberian Obat & Reversal Stok',

            // Phase 3A Permissions
            'view-healthcare-partners' => 'Lihat Mitra Layanan Kesehatan',
            'manage-healthcare-partners' => 'Kelola Master Data Mitra Kesehatan',
            'verify-healthcare-partner-contacts' => 'Verifikasi Kontak Medis Mitra',
            'view-clinical-consultations' => 'Lihat Direktori Konsultasi Eksternal',
            'create-clinical-consultations' => 'Buat Konsultasi Klinis Eksternal',
            'finalize-clinical-consultation-summaries' => 'Finalisasi Ringkasan Konsultasi',
            'send-clinical-consultations' => 'Kirim Ringkasan Konsultasi ke Mitra',
            'cancel-clinical-consultations' => 'Batalkan Pengajuan Konsultasi',
            'record-external-clinical-advice' => 'Catat Jawaban/Advice Klinis Eksternal',
            'verify-external-clinical-advice' => 'Verifikasi Advice Klinis Eksternal',
            'finalize-local-clinical-decisions' => 'Penetapan Keputusan Klinis Lokal',
            'download-clinical-consultation-documents' => 'Unduh Dokumen PDF Konsultasi',
            'view-clinical-consultation-transmissions' => 'Lihat Log Transmisi Konsultasi',

            // Phase 3B Permissions (Referral)
            'view-referrals' => 'Lihat Direktori & Timeline Rujukan',
            'create-referrals' => 'Buat Rujukan Medis Baru',
            'approve-referrals' => 'Persetujuan Medis Surat Rujukan',
            'prepare-referral-documents' => 'Siapkan Dokumen Berkas Rujukan',
            'arrange-referral-transport' => 'Atur Armada Transportasi Rujukan',
            'assign-referral-companions' => 'Tugaskan Pendamping Rujukan',
            'record-referral-departure' => 'Catat Keberangkatan Rujukan',
            'record-referral-handover' => 'Catat Serah Terima Klinis Faskes',
            'record-destination-status' => 'Catat Status Pelayanan Faskes Tujuan',
            'record-referral-returns' => 'Catat Kepulangan Pasien Rujukan',
            'review-referral-returns' => 'Telaah Medis Kepulangan Rujukan',
            'cancel-referrals' => 'Batalkan Rujukan Medis',
            'download-referral-documents' => 'Unduh Surat & Berkas Rujukan',

            // Phase 3C Permissions (Discharge, Handoff, Activity Restriction, Follow Up)
            'view-discharges' => 'Lihat Kepulangan Medis',
            'create-discharges' => 'Pencatatan Kepulangan Medis',
            'finalize-discharges' => 'Finalisasi Resume Kepulangan',
            'download-discharge-documents' => 'Unduh Resume Medis & Instruksi',
            'view-operational-handoffs' => 'Lihat Handoff Asrama',
            'prepare-operational-handoffs' => 'Buat Lembar Handoff Asrama',
            'acknowledge-operational-handoffs' => 'Konfirmasi Penerimaan Handoff',
            'view-activity-restrictions' => 'Lihat Pembatasan Aktivitas',
            'manage-activity-restrictions' => 'Kelola Pembatasan Aktivitas Santri',
            'view-follow-up-plans' => 'Lihat Rencana Kontrol',
            'create-follow-up-plans' => 'Buat Rencana Kontrol Baru',
            'complete-follow-up-plans' => 'Penyelesaian Jadwal Kontrol',
            'cancel-follow-up-plans' => 'Batalkan Jadwal Kontrol',

            // Phase 4 Permissions (Notification & Integration Outbox)
            'view-operational-notifications' => 'Lihat Notifikasi Operasional',
            'prepare-operational-notifications' => 'Buat Notifikasi Operasional',
            'acknowledge-operational-notifications' => 'Konfirmasi Notifikasi Asrama',
            'view-attendance-integration-settings' => 'Lihat Integrasi Presensi',
            'manage-attendance-integration-settings' => 'Kelola Pengaturan Presensi',
            'view-integration-outbox' => 'Lihat Outbox Integrasi Event',
            'replay-integration-outbox' => 'Kirim Ulang Event Integrasi',
            'manage-identity-mappings' => 'Kelola Pemetaan Identitas Santri',
            'execute-gate-sync-apply' => 'Eksekusi Sinkronisasi Gate',
            'view-gate-reconciliation' => 'Lihat Laporan Rekonsiliasi Gate',

            // Phase 5C Dashboard & Reporting Permissions
            'view-clinical-dashboard' => 'Lihat Dashboard Klinis Poskestren',
            'view-operational-dashboard' => 'Lihat Dashboard Operasional Asrama',
            'view-management-dashboard' => 'Lihat Dashboard Manajemen Eksekutif',
            'view-pharmacy-dashboard' => 'Lihat Dashboard Farmasi & Obat',
            'view-health-reports' => 'Lihat Laporan Kesehatan & Sensus',
            'export-health-reports' => 'Ekspor Laporan Kesehatan (CSV)',
        ];

        $createdPermissions = [];
        foreach ($permissions as $name => $displayName) {
            $createdPermissions[$name] = Permission::firstOrCreate([
                'name' => $name,
            ], [
                'display_name' => $displayName,
                'description' => "Hak akses untuk {$displayName}",
            ]);
        }

        // 1. Role Super Admin (Full Universal Access)
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
        ], [
            'display_name' => 'Super Administrator',
            'description' => 'Akses penuh dan wewenang mutlak atas seluruh modul dan konfigurasi sistem POSKESTREN',
        ]);
        $superAdminRole->permissions()->syncWithoutDetaching(array_values(array_map(fn ($p) => $p->id, $createdPermissions)));

        // 2. Role Admin Poskestren (Delegated Administration)
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrator POSKESTREN',
            'description' => 'Pengelola sistem identitas, pengguna, peran, dan konfigurasi integrasi Gate SSO',
        ]);
        $adminRole->permissions()->syncWithoutDetaching([
            $createdPermissions['manage-users']->id,
            $createdPermissions['manage-roles']->id,
            $createdPermissions['manage-permissions']->id,
            $createdPermissions['view-people']->id,
            $createdPermissions['manage-gate-sync']->id,
            $createdPermissions['view-gate-sync']->id,
            $createdPermissions['resolve-identity-conflicts']->id,
            $createdPermissions['view-audit-log']->id,
            $createdPermissions['manage-system-settings']->id,
            $createdPermissions['manage-healthcare-partners']->id,
            $createdPermissions['view-healthcare-partners']->id,
            $createdPermissions['view-management-dashboard']->id,
            $createdPermissions['view-health-reports']->id,
            $createdPermissions['export-health-reports']->id,
        ]);

        // 3. Role Petugas Kesehatan (Clinical & Medical Staff)
        $medicalRole = Role::firstOrCreate([
            'name' => 'petugas_kesehatan',
        ], [
            'display_name' => 'Tim Kesehatan POSKESTREN',
            'description' => 'Dokter dan perawat yang melayani pendaftaran, pengkajian klinis, observasi, konsultasi, dan rujukan santri',
        ]);
        $medicalExcluded = [
            'manage-users', 'manage-roles', 'manage-permissions', 'view-people',
            'manage-gate-sync', 'view-gate-sync', 'resolve-identity-conflicts',
            'manage-system-settings', 'view-audit-log', 'manage-healthcare-partners',
            'view-management-dashboard', 'view-operational-dashboard',
        ];
        $medicalRole->permissions()->syncWithoutDetaching(array_values(array_map(fn ($p) => $p->id, array_diff_key($createdPermissions, array_flip($medicalExcluded)))));

        // 4. Role Farmasi / Apoteker
        $pharmacyRole = Role::firstOrCreate([
            'name' => 'farmasi',
        ], [
            'display_name' => 'Apoteker & Petugas Farmasi',
            'description' => 'Pengelola stok inventaris obat, penerimaan batch, mutasi, dan dispensing obat',
        ]);
        $pharmacyPermKeys = [
            'view-pharmacy-dashboard', 'view-pharmacy-inventory', 'manage-medicine-master', 'receive-medicine-stock',
            'adjust-medicine-stock', 'reverse-stock-movements', 'transfer-medicine-stock', 'view-stock-movements',
            'view-stock-reconciliation', 'manage-stock-locations', 'view-medication-orders', 'create-medication-orders',
            'activate-medication-orders', 'revise-medication-orders', 'discontinue-medication-orders',
            'view-medication-administrations', 'schedule-medication-administrations', 'administer-medications',
            'administer-one-time-medication', 'hold-medications', 'record-medication-refusal',
            'record-missed-medication', 'correct-medication-administrations', 'view-health-reports', 'export-health-reports',
        ];
        $pharmacyPermIds = [];
        foreach ($pharmacyPermKeys as $key) {
            if (isset($createdPermissions[$key])) {
                $pharmacyPermIds[] = $createdPermissions[$key]->id;
            }
        }
        $pharmacyRole->permissions()->syncWithoutDetaching($pharmacyPermIds);

        // 5. Role Pengasuh Asrama (Operational)
        $operationalRole = Role::firstOrCreate([
            'name' => 'pengasuh_asrama',
        ], [
            'display_name' => 'Pengasuh Asrama / Staf Operasional',
            'description' => 'Menerima instruksi pemantauan santri pasca rawat dan pembatasan aktivitas fisik',
        ]);
        $operationalPermKeys = [
            'view-operational-dashboard', 'view-operational-handoffs', 'prepare-operational-handoffs',
            'acknowledge-operational-handoffs', 'view-operational-notifications', 'prepare-operational-notifications',
            'acknowledge-operational-notifications',
        ];
        $operationalPermIds = [];
        foreach ($operationalPermKeys as $key) {
            if (isset($createdPermissions[$key])) {
                $operationalPermIds[] = $createdPermissions[$key]->id;
            }
        }
        $operationalRole->permissions()->syncWithoutDetaching($operationalPermIds);

        // 6. Role Manajemen Eksekutif (Management)
        $managementRole = Role::firstOrCreate([
            'name' => 'manajemen',
        ], [
            'display_name' => 'Manajemen & Pimpinan Pesantren',
            'description' => 'Melihat statistik agregat pelayanan kesehatan santri dan laporan eksekutif',
        ]);
        $managementRole->permissions()->syncWithoutDetaching([
            $createdPermissions['view-management-dashboard']->id,
            $createdPermissions['view-health-reports']->id,
            $createdPermissions['export-health-reports']->id,
        ]);

        // Default Stock Location
        StockLocation::firstOrCreate([
            'code' => 'PHARMACY_MAIN',
        ], [
            'name' => 'Ruang Apotek Utama Poskestren',
            'description' => 'Gudang & penyimpanan utama obat-obatan Poskestren',
            'is_active' => true,
        ]);

        // Synthetic identities, credentials, partner contacts, and clinical
        // fixtures must never be created implicitly in staging/production.
        if (! config('app.seed_demo_data', false)) {
            return;
        }

        // Default Healthcare Partner Facility
        $partner = HealthcarePartner::firstOrCreate([
            'code' => 'PUSKESMAS-AMPEL',
        ], [
            'name' => 'Puskesmas Pembantu / Kecamatan Ampel',
            'partner_type' => 'puskesmas',
            'address' => 'Jl. Raya Ampel No. 12, Surabaya',
            'phone' => '031-5550199',
            'official_email' => 'pkm.ampel@surabaya.go.id',
            'cooperation_reference' => 'MOU-POSKESTREN-2026/01',
            'is_active' => true,
            'consultation_enabled' => true,
            'referral_enabled' => true,
            'default_channel' => 'fake_transport',
        ]);

        HealthcarePartnerContact::firstOrCreate([
            'healthcare_partner_id' => $partner->id,
            'name' => 'dr. H. Ahmad Dahlan, Sp.PD',
        ], [
            'profession' => 'Dokter Spesialis Penyakit Dalam',
            'registration_identifier' => 'SIP-3578/2025/8812',
            'department' => 'Poli Penyakit Dalam / Konsultasi Faskes',
            'official_contact' => '0812-3456-7890',
            'channel_type' => 'fake_transport',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        // Create Seed Person & Admin User
        $adminPerson = Person::firstOrCreate(
            ['email' => 'admin@poskestren.sabira.test'],
            [
                'name' => 'Admin Utama Poskestren',
                'user_type' => 'admin',
            ]
        );

        $patient = Patient::createOrFindForPerson($adminPerson, ['is_eligible' => true]);

        PatientHealthProfile::firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'blood_type' => 'O+',
                'emergency_notes' => 'Tidak ada penyakit kronis bawaan.',
            ]
        );

        PatientAllergy::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'allergen' => 'Penicillin',
            ],
            [
                'reaction' => 'Ruam kulit & gatal',
                'severity' => 'moderate',
                'clinical_status' => 'active',
                'verification_status' => 'confirmed',
                'notes' => 'Tercatat dari riwayat masa lalu',
            ]
        );

        PatientMedicalCondition::firstOrCreate(
            [
                'patient_id' => $patient->id,
                'condition_name' => 'Asma Bronkial',
            ],
            [
                'status' => 'active',
                'notes' => 'Kambuh bila dingin ekstrem',
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@poskestren.sabira.test'],
            [
                'person_id' => $adminPerson->id,
                'name' => $adminPerson->name,
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id, $superAdminRole->id]);

        // Create Doctor/Nurse User
        $doctorPerson = Person::firstOrCreate(
            ['email' => 'fatimah.medis@sabira.test'],
            [
                'name' => 'dr. Fatimah Medis',
                'user_type' => 'petugas_kesehatan',
            ]
        );
        $doctorUser = User::firstOrCreate(
            ['email' => 'fatimah.medis@sabira.test'],
            [
                'person_id' => $doctorPerson->id,
                'name' => $doctorPerson->name,
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $doctorUser->roles()->syncWithoutDetaching([$medicalRole->id]);

        // Create Pharmacy User
        $pharmacyPerson = Person::firstOrCreate(
            ['email' => 'apoteker@sabira.test'],
            [
                'name' => 'Ahmad Apoteker, S.Farm',
                'user_type' => 'petugas_kesehatan',
            ]
        );
        $pharmacyUser = User::firstOrCreate(
            ['email' => 'apoteker@sabira.test'],
            [
                'person_id' => $pharmacyPerson->id,
                'name' => $pharmacyPerson->name,
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $pharmacyUser->roles()->syncWithoutDetaching([$pharmacyRole->id]);

        // Create Operational / Asrama User
        $musyrifPerson = Person::firstOrCreate(
            ['email' => 'musyrif@sabira.test'],
            [
                'name' => 'Ustadz Abdullah (Musyrif Asrama)',
                'user_type' => 'pengasuh_asrama',
            ]
        );
        $musyrifUser = User::firstOrCreate(
            ['email' => 'musyrif@sabira.test'],
            [
                'person_id' => $musyrifPerson->id,
                'name' => $musyrifPerson->name,
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $musyrifUser->roles()->syncWithoutDetaching([$operationalRole->id]);

        // Create Management User
        $managementPerson = Person::firstOrCreate(
            ['email' => 'pimpinan@sabira.test'],
            [
                'name' => 'Kyai Pengasuh Pesantren',
                'user_type' => 'manajemen',
            ]
        );
        $managementUser = User::firstOrCreate(
            ['email' => 'pimpinan@sabira.test'],
            [
                'person_id' => $managementPerson->id,
                'name' => $managementPerson->name,
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $managementUser->roles()->syncWithoutDetaching([$managementRole->id]);
    }
}
