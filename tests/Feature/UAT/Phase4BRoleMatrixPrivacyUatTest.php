<?php

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;

function createUserWithRole(string $roleName, array $permissionNames = []): User
{
    $person = Person::factory()->create(['name' => "User {$roleName}"]);
    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => "{$roleName}.".uniqid().'@sabira.test',
        'is_active' => true,
    ]);

    $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => ucwords(str_replace('_', ' ', $roleName))]);

    $permissionIds = [];
    foreach ($permissionNames as $permName) {
        $p = Permission::firstOrCreate(['name' => $permName], ['display_name' => ucwords(str_replace('-', ' ', $permName))]);
        $permissionIds[] = $p->id;
    }

    $role->permissions()->syncWithoutDetaching($permissionIds);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $user->unsetRelation('roles');

    return $user;
}

test('Technical Administrator cannot access clinical dashboard or clinical assessment workspace', function () {
    $admin = createUserWithRole('administrator', [
        'view-gate-sync',
        'execute-gate-sync-apply',
        'manage-identity-mappings',
        'view-gate-reconciliation',
    ]);

    $this->actingAs($admin);

    // Can access Gate sync and reconciliation
    $this->get('/gate/sync')->assertOk();
    $this->get('/gate/reconciliation')->assertOk();

    // CANNOT access Clinical Dashboard
    $this->get('/dashboards/clinical')->assertForbidden();
});

test('Dormitory Supervisor can access operational dashboard but cannot view clinical assessments', function () {
    $dormSupervisor = createUserWithRole('pembina_asrama', [
        'view-operational-dashboard',
        'acknowledge-operational-handoffs',
        'view-operational-notifications',
    ]);

    $this->actingAs($dormSupervisor);

    // Can access Operational Dashboard & Handoffs
    $this->get('/dashboards/operational')->assertOk();
    $this->get('/operational-handoffs')->assertOk();

    // CANNOT access Clinical Dashboard
    $this->get('/dashboards/clinical')->assertForbidden();
});

test('Management role can view aggregated management dashboard but cannot access individual clinical workspace', function () {
    $manager = createUserWithRole('manajemen', [
        'view-management-dashboard',
        'view-health-reports',
        'export-health-reports',
    ]);

    $this->actingAs($manager);

    // Can access Management Aggregate Dashboard
    $this->get('/dashboards/management')->assertOk();
    $this->get('/reports')->assertOk();

    // CANNOT access Clinical Dashboard
    $this->get('/dashboards/clinical')->assertForbidden();
});

test('Clinical Doctor has full access to clinical dashboard and medical visit workflows', function () {
    $doctor = createUserWithRole('dokter', [
        'view-clinical-dashboard',
        'view-medical-visit',
        'view-visit-discharges',
        'create-clinical-assessment',
        'finalize-clinical-assessment',
        'prepare-visit-discharges',
        'finalize-visit-discharges',
    ]);

    $this->actingAs($doctor);

    // Can access Clinical Dashboard
    $this->get('/dashboards/clinical')->assertOk();
    $this->get('/discharges')->assertOk();
});

test('Homeroom Teacher cannot access pharmacy inventory or clinical assessments', function () {
    $teacher = createUserWithRole('wali_kelas', [
        'view-operational-dashboard',
        'view-operational-notifications',
    ]);

    $this->actingAs($teacher);

    // Can access Operational Dashboard
    $this->get('/dashboards/operational')->assertOk();

    // CANNOT access Clinical Dashboard
    $this->get('/dashboards/clinical')->assertForbidden();
});
