<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view-operational-notifications' => 'Melihat data notifikasi operasional internal',
            'prepare-operational-notifications' => 'Menyiapkan notifikasi operasional internal',
            'acknowledge-operational-notifications' => 'Mengonfirmasi penerimaan notifikasi operasional',
            'view-integration-outbox' => 'Melihat antrean event outbox integrasi',
            'retry-integration-events' => 'Menjalankan ulang (retry) event integrasi yang gagal',
            'resolve-integration-conflicts' => 'Menyelesaikan konflik identitas pemetaan integrasi',
            'view-attendance-integration-status' => 'Melihat status konektor integrasi Absensi',
            'manage-attendance-integration-settings' => 'Mengelola pengaturan konektor integrasi Absensi',
            'view-clinical-dashboard' => 'Melihat dashboard operasional klinis poskestren',
            'view-management-dashboard' => 'Melihat dashboard agregat manajemen poskestren',
            'view-operational-dashboard' => 'Melihat dashboard operasional asrama dan sekolah',
            'view-health-reports' => 'Melihat laporan dan sensus kesehatan',
            'export-health-reports' => 'Mengekspor data laporan kesehatan',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // Assign default role permissions
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorPerms = Permission::whereIn('name', [
                'view-operational-notifications',
                'prepare-operational-notifications',
                'acknowledge-operational-notifications',
                'view-clinical-dashboard',
                'view-operational-dashboard',
                'view-health-reports',
                'export-health-reports',
            ])->pluck('id');
            $doctorRole->permissions()->syncWithoutDetaching($doctorPerms);
        }

        $nurseRole = Role::where('name', 'nurse')->first();
        if ($nurseRole) {
            $nursePerms = Permission::whereIn('name', [
                'view-operational-notifications',
                'prepare-operational-notifications',
                'acknowledge-operational-notifications',
                'view-clinical-dashboard',
                'view-operational-dashboard',
                'view-health-reports',
            ])->pluck('id');
            $nurseRole->permissions()->syncWithoutDetaching($nursePerms);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminPerms = Permission::whereIn('name', [
                'view-integration-outbox',
                'retry-integration-events',
                'resolve-integration-conflicts',
                'view-attendance-integration-status',
                'manage-attendance-integration-settings',
                'view-management-dashboard',
                'view-health-reports',
            ])->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($adminPerms);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $names = [
            'view-operational-notifications',
            'prepare-operational-notifications',
            'acknowledge-operational-notifications',
            'view-integration-outbox',
            'retry-integration-events',
            'resolve-integration-conflicts',
            'view-attendance-integration-status',
            'manage-attendance-integration-settings',
            'view-clinical-dashboard',
            'view-management-dashboard',
            'view-operational-dashboard',
            'view-health-reports',
            'export-health-reports',
        ];

        Permission::whereIn('name', $names)->delete();
    }
};
