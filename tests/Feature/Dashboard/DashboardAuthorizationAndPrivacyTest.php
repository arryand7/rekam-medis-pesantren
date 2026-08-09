<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Dashboard\ManagementDashboardService;

function createTestUserWithPerms(array $permNames): User
{
    $roleName = 'test_role_'.md5(implode('_', $permNames));
    $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => $roleName]);
    foreach ($permNames as $name) {
        $perm = Permission::where('name', $name)->first();
        if (! $perm) {
            $perm = Permission::create(['name' => $name, 'display_name' => $name]);
        }
        if (! $role->permissions()->where('permission_id', $perm->id)->exists()) {
            $role->permissions()->attach($perm->id);
        }
    }

    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    return $user;
}

test('guest is redirected to login on dashboard endpoints', function () {
    $this->get('/dashboards/clinical')->assertRedirect('/login');
    $this->get('/dashboards/management')->assertRedirect('/login');
    $this->get('/dashboards/operational')->assertRedirect('/login');
});

test('clinical dashboard endpoint requires view-clinical-dashboard permission', function () {
    $unprivileged = User::factory()->create();
    $this->actingAs($unprivileged)->get('/dashboards/clinical')->assertForbidden();

    $doctor = createTestUserWithPerms(['view-clinical-dashboard']);
    $this->actingAs($doctor)->get('/dashboards/clinical')->assertOk();
});

test('management dashboard endpoint strictly contains only aggregate metrics without patient records', function () {
    $service = new ManagementDashboardService;
    $metrics = $service->getAggregatedMetrics();

    // Verify key aggregate fields exist
    expect($metrics)->toHaveKeys([
        'period',
        'total_visits',
        'total_observations',
        'total_referrals',
        'total_discharges',
        'follow_up_completion_rate',
        'low_stock_medicines_count',
    ]);

    // Ensure no patient arrays or medical text narratives are included
    expect($metrics)->not->toHaveKey('patients');
    expect($metrics)->not->toHaveKey('diagnoses');
    expect($metrics)->not->toHaveKey('prescriptions');
});

test('operational dashboard endpoint requires view-operational-dashboard permission', function () {
    $unprivileged = User::factory()->create();
    $this->actingAs($unprivileged)->get('/dashboards/operational')->assertForbidden();

    $dormAdmin = createTestUserWithPerms(['view-operational-dashboard']);
    $this->actingAs($dormAdmin)->get('/dashboards/operational')->assertOk();
});
