<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('demo identities require explicit opt in', function () {
    config()->set('app.seed_demo_data', false);

    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'admin@poskestren.sabira.test')->exists())->toBeFalse()
        ->and(Role::where('name', 'super_admin')->exists())->toBeTrue()
        ->and(Permission::where('name', 'manage-users')->exists())->toBeTrue();
});

test('rbac seed is idempotent and preserves additional local grants', function () {
    config()->set('app.seed_demo_data', false);
    $this->seed(DatabaseSeeder::class);

    $role = Role::where('name', 'manajemen')->firstOrFail();
    $permission = Permission::where('name', 'view-people')->firstOrFail();
    $role->permissions()->syncWithoutDetaching([$permission->id]);

    $beforeRoleCount = Role::count();
    $beforePermissionCount = Permission::count();

    $this->seed(DatabaseSeeder::class);

    expect(Role::count())->toBe($beforeRoleCount)
        ->and(Permission::count())->toBe($beforePermissionCount)
        ->and($role->fresh()->permissions()->whereKey($permission->id)->exists())->toBeTrue();
});
