<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

function createBrandingManager(): User
{
    return createBrandingUserWithRole('branding_manager', true);
}

function createBrandingUserWithRole(string $roleName, bool $withPermission = false): User
{
    $name = $roleName === 'super_admin' ? $roleName : $roleName.'_'.uniqid();
    $role = Role::firstOrCreate(
        ['name' => $name],
        ['display_name' => str($roleName)->headline()->toString()]
    );

    if ($withPermission) {
        $permission = Permission::firstOrCreate(
            ['name' => 'manage-system-settings'],
            ['display_name' => 'Kelola Pengaturan Sistem']
        );
        $role->permissions()->syncWithoutDetaching($permission);
    }

    $user = User::factory()->create();
    $user->roles()->attach($role);

    return $user;
}

/** @return array<string, string> */
function brandingPayload(array $overrides = []): array
{
    return array_merge([
        'application_name' => 'SABIRA Health Test',
        'application_short_name' => 'SAHAT',
        'institution_name' => 'Institusi Sintetis',
        'tagline' => 'Sehat, Aman, Terlayani',
        'description' => 'Deskripsi branding sintetis untuk pengujian.',
        'footer_text' => 'Institusi Sintetis — Layanan Kesehatan',
    ], $overrides);
}
