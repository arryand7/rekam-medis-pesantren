<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
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

        // 2. Role Petugas Kesehatan (Medical Staff)
        $medicalRole = Role::firstOrCreate([
            'name' => 'petugas_kesehatan',
        ], [
            'display_name' => 'Tim Kesehatan POSKESTREN',
            'description' => 'Tenaga medis/kesehatan yang melayani santri dan warga di Poskestren',
        ]);
        $medicalRole->permissions()->sync([
            $createdPermissions['view-people']->id,
            $createdPermissions['view-patients']->id,
        ]);

        // Create Seed Person & Admin User
        $adminPerson = Person::factory()->create([
            'name' => 'Admin Utama Poskestren',
            'user_type' => 'admin',
            'email' => 'admin@poskestren.sabira.test',
        ]);

        // Patient profile for Admin Person (All humans are eligible)
        Patient::factory()->create([
            'person_id' => $adminPerson->id,
            'is_eligible' => true,
        ]);

        $adminUser = User::factory()->create([
            'person_id' => $adminPerson->id,
            'name' => $adminPerson->name,
            'email' => 'admin@poskestren.sabira.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->id);
    }
}
