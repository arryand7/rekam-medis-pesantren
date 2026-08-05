<?php

namespace Database\Seeders;

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

        // 1. Role Admin Poskestren
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrator POSKESTREN',
            'description' => 'Pengelola sistem identitas, pengguna, dan konfigurasi integrasi Gate SSO',
        ]);
        $adminRole->permissions()->sync([
            $createdPermissions['manage-users']->id,
            $createdPermissions['manage-roles']->id,
            $createdPermissions['manage-permissions']->id,
            $createdPermissions['view-people']->id,
            $createdPermissions['manage-gate-sync']->id,
            $createdPermissions['view-gate-sync']->id,
            $createdPermissions['resolve-identity-conflicts']->id,
            $createdPermissions['view-audit-log']->id,
            $createdPermissions['manage-system-settings']->id,
        ]);

        // 2. Role Petugas Kesehatan & Farmasi (Medical & Pharmacy Staff)
        $medicalRole = Role::firstOrCreate([
            'name' => 'petugas_kesehatan',
        ], [
            'display_name' => 'Tim Kesehatan POSKESTREN',
            'description' => 'Tenaga medis/kesehatan dan farmasi yang melayani santri dan warga di Poskestren',
        ]);
        $medicalRole->permissions()->sync([
            $createdPermissions['view-people']->id,
            $createdPermissions['view-patients']->id,
            $createdPermissions['view-patient-health-profile']->id,
            $createdPermissions['update-patient-health-profile']->id,
            $createdPermissions['manage-patient-allergies']->id,
            $createdPermissions['manage-patient-conditions']->id,
            $createdPermissions['manage-emergency-contacts']->id,
            $createdPermissions['create-medical-visits']->id,
            $createdPermissions['view-medical-visits']->id,
            $createdPermissions['cancel-medical-visits']->id,
            $createdPermissions['override-active-visit']->id,
            $createdPermissions['record-vital-signs']->id,
            $createdPermissions['finalize-vital-signs']->id,
            $createdPermissions['create-clinical-assessments']->id,
            $createdPermissions['finalize-clinical-assessments']->id,
            $createdPermissions['amend-clinical-assessments']->id,
            $createdPermissions['record-working-diagnosis']->id,
            $createdPermissions['record-initial-actions']->id,
            $createdPermissions['recommend-visit-disposition']->id,
            $createdPermissions['start-observations']->id,
            $createdPermissions['view-observations']->id,
            $createdPermissions['record-observation-monitoring']->id,
            $createdPermissions['finalize-observation-monitoring']->id,
            $createdPermissions['amend-observation-monitoring']->id,
            $createdPermissions['prepare-observation-handover']->id,
            $createdPermissions['acknowledge-observation-handover']->id,
            $createdPermissions['complete-observations']->id,
            $createdPermissions['cancel-observations']->id,
            $createdPermissions['view-observation-audit']->id,
            $createdPermissions['view-pharmacy-inventory']->id,
            $createdPermissions['manage-medicine-master']->id,
            $createdPermissions['receive-medicine-stock']->id,
            $createdPermissions['adjust-medicine-stock']->id,
            $createdPermissions['reverse-stock-movements']->id,
            $createdPermissions['transfer-medicine-stock']->id,
            $createdPermissions['view-stock-movements']->id,
            $createdPermissions['view-stock-reconciliation']->id,
            $createdPermissions['manage-stock-locations']->id,
        ]);

        // Default Stock Location
        StockLocation::firstOrCreate([
            'code' => 'PHARMACY_MAIN',
        ], [
            'name' => 'Ruang Apotek Utama Poskestren',
            'description' => 'Gudang & penyimpanan utama obat-obatan Poskestren',
            'is_active' => true,
        ]);

        // Create Seed Person & Admin User
        $adminPerson = Person::factory()->create([
            'name' => 'Admin Utama Poskestren',
            'user_type' => 'admin',
            'email' => 'admin@poskestren.sabira.test',
        ]);

        $patient = Patient::factory()->create([
            'person_id' => $adminPerson->id,
            'is_eligible' => true,
        ]);

        PatientHealthProfile::create([
            'patient_id' => $patient->id,
            'blood_type' => 'O+',
            'emergency_notes' => 'Tidak ada penyakit kronis bawaan.',
        ]);

        PatientAllergy::create([
            'patient_id' => $patient->id,
            'allergen' => 'Penicillin',
            'reaction' => 'Ruam kulit & gatal',
            'severity' => 'moderate',
            'clinical_status' => 'active',
            'verification_status' => 'confirmed',
            'notes' => 'Tercatat dari riwayat masa lalu',
        ]);

        PatientMedicalCondition::create([
            'patient_id' => $patient->id,
            'condition_name' => 'Asma Bronkial',
            'status' => 'active',
            'notes' => 'Kambuh bila dingin ekstrem',
        ]);

        $adminUser = User::factory()->create([
            'person_id' => $adminPerson->id,
            'name' => $adminPerson->name,
            'email' => 'admin@poskestren.sabira.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->id);

        // Create Doctor/Nurse User
        $doctorPerson = Person::factory()->create([
            'name' => 'dr. Fatimah Medis',
            'user_type' => 'petugas_kesehatan',
            'email' => 'fatimah.medis@sabira.test',
        ]);
        $doctorUser = User::factory()->create([
            'person_id' => $doctorPerson->id,
            'name' => $doctorPerson->name,
            'email' => 'fatimah.medis@sabira.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $doctorUser->roles()->attach($medicalRole->id);
    }
}
