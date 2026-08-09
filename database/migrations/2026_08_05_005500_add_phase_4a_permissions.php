<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'view-gate-sync' => 'Melihat status sinkronisasi identitas Gate',
            'execute-gate-sync-apply' => 'Mengeksekusi apply sinkronisasi data Gate ke lokal',
            'manage-identity-mappings' => 'Mengelola pemetaan identitas person Gate',
            'view-gate-reconciliation' => 'Melihat laporan rekonsiliasi identitas Gate',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // Attach to administrator role if exists
        $adminRole = Role::where('name', 'administrator')->orWhere('name', 'super_admin')->first();
        if ($adminRole) {
            $permIds = Permission::whereIn('name', array_keys($permissions))->pluck('id')->toArray();
            $adminRole->permissions()->syncWithoutDetaching($permIds);
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'view-gate-sync',
            'execute-gate-sync-apply',
            'manage-identity-mappings',
            'view-gate-reconciliation',
        ])->delete();
    }
};
