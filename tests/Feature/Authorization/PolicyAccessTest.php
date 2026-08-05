<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PatientPolicy;

test('admin role does not automatically grant medical record access without view-patients permission', function () {
    $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
    $user = User::factory()->create();
    $user->roles()->attach($adminRole->id);

    $policy = new PatientPolicy;

    // User without view-patients permission should be denied
    expect($policy->viewAny($user))->toBeFalse();

    // Grant view-patients permission
    $perm = Permission::create(['name' => 'view-patients', 'display_name' => 'View Patients']);
    $adminRole->permissions()->attach($perm->id);

    expect($user->fresh()->hasPermission('view-patients'))->toBeTrue();
    expect($policy->viewAny($user->fresh()))->toBeTrue();
});
