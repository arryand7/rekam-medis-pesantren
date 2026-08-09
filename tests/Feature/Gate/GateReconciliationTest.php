<?php

use App\Models\GateIdentityMapping;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;

function createReconciliationAdmin(): User
{
    $user = User::factory()->create();
    $role = Role::create(['name' => 'identity_manager_'.uniqid(), 'display_name' => 'Identity Manager']);

    $perm1 = Permission::firstOrCreate(['name' => 'view-gate-reconciliation'], ['display_name' => 'View Reconciliation']);
    $perm2 = Permission::firstOrCreate(['name' => 'manage-identity-mappings'], ['display_name' => 'Manage Identity Mappings']);

    $role->permissions()->attach([$perm1->id, $perm2->id]);
    $user->roles()->attach($role->id);

    return $user;
}

test('guest cannot access gate reconciliation endpoints', function () {
    $this->get('/gate/reconciliation')->assertRedirect('/login');
});

test('user with permission can view reconciliation overview', function () {
    $admin = createReconciliationAdmin();
    $this->actingAs($admin);

    $response = $this->get('/gate/reconciliation');

    $response->assertOk();
    $response->assertSee('Rekonsiliasi');
});

test('approving candidate mapping links gate_user_id to person and updates mapping status', function () {
    $admin = createReconciliationAdmin();
    $this->actingAs($admin);

    $person = Person::factory()->create([
        'gate_user_id' => null,
        'name' => 'Person Tanpa Gate ID',
    ]);

    $mapping = GateIdentityMapping::create([
        'gate_user_id' => 'GATE-USR-REC-001',
        'person_id' => $person->id,
        'mapping_method' => 'nis_match',
        'confidence_score' => 0.90,
        'status' => 'pending',
    ]);

    $response = $this->post("/gate/reconciliation/{$mapping->id}/approve", [
        'notes' => 'Disetujui berdasarkan kesesuaian NIS dan identitas asrama.',
    ]);

    $response->assertRedirect();
    $mapping->refresh();
    expect($mapping->status)->toBe('approved');
    expect($mapping->approved_by_id)->toBe($admin->id);

    $person->refresh();
    expect($person->gate_user_id)->toBe('GATE-USR-REC-001');
});
