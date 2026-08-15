<?php

namespace Tests\Feature\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected User $medicalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::where('email', 'admin@poskestren.sabira.test')->first();
        $this->medicalUser = User::where('email', 'fatimah.medis@sabira.test')->first();

        // Create dedicated normal admin without super_admin role
        $this->admin = User::factory()->create(['is_active' => true]);
        $adminRole = Role::where('name', 'admin')->first();
        $this->admin->roles()->sync([$adminRole->id]);
    }

    public function test_non_admin_cannot_access_rbac_routes(): void
    {
        $this->actingAs($this->medicalUser)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($this->medicalUser)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($this->medicalUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->medicalUser)->post(route('roles.store'), ['name' => 'test'])->assertForbidden();
    }

    public function test_normal_admin_cannot_assign_super_admin_role(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);
        $superAdminRole = Role::where('name', 'super_admin')->first();

        $response = $this->actingAs($this->admin)->post(route('users.roles.update', $targetUser->id), [
            'roles' => [$superAdminRole->id],
        ]);

        $response->assertForbidden();
        $targetUser->refresh();
        $this->assertFalse($targetUser->isSuperAdmin());
    }

    public function test_normal_admin_cannot_assign_or_strip_protected_admin_role(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($this->admin)->post(route('users.roles.update', $targetUser->id), [
            'roles' => [$adminRole->id],
        ])->assertForbidden();

        $targetUser->roles()->sync([$adminRole->id]);

        $this->actingAs($this->admin)->post(route('users.roles.update', $targetUser->id), [
            'roles' => [],
        ])->assertForbidden();

        $this->assertTrue($targetUser->fresh()->roles()->where('name', 'admin')->exists());
    }

    public function test_normal_admin_cannot_modify_super_admin_role(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();

        $response = $this->actingAs($this->admin)->put(route('roles.update', $superAdminRole->id), [
            'name' => 'super_admin',
            'display_name' => 'Compromised Super Admin',
            'permissions' => ['view-patients'],
        ]);

        $response->assertForbidden();
        $superAdminRole->refresh();
        $this->assertEquals('Super Administrator', $superAdminRole->display_name);
    }

    public function test_normal_admin_cannot_modify_protected_admin_role(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($this->admin)->put(route('roles.update', $adminRole->id), [
            'name' => 'admin',
            'display_name' => 'Compromised Admin',
            'permissions' => ['view-patients'],
        ])->assertForbidden();

        expect($adminRole->fresh()->display_name)->toBe('Administrator POSKESTREN');
    }

    public function test_normal_admin_cannot_grant_protected_permissions_in_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'sub_admin',
            'display_name' => 'Sub Admin',
            'permissions' => ['manage-gate-sync'], // Protected permission
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'sub_admin']);
    }

    public function test_normal_admin_cannot_grant_protected_permissions_directly_to_users(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('users.permissions.update', $targetUser->id), [
            'permissions' => ['manage-roles'], // Protected permission
        ]);

        $response->assertForbidden();
        $targetUser->refresh();
        $this->assertFalse($targetUser->hasPermission('manage-roles'));
    }

    public function test_normal_admin_cannot_strip_protected_permissions_from_role_or_user(): void
    {
        $protectedPermission = Permission::where('name', 'manage-gate-sync')->firstOrFail();
        $customRole = Role::create([
            'name' => 'custom_protected',
            'display_name' => 'Custom Protected',
        ]);
        $customRole->permissions()->sync([$protectedPermission->id]);

        $this->actingAs($this->admin)->put(route('roles.update', $customRole->id), [
            'name' => $customRole->name,
            'display_name' => $customRole->display_name,
            'permissions' => [],
        ])->assertForbidden();

        $targetUser = User::factory()->create(['is_active' => true]);
        $targetUser->permissions()->sync([$protectedPermission->id]);

        $this->actingAs($this->admin)->post(route('users.permissions.update', $targetUser->id), [
            'permissions' => [],
        ])->assertForbidden();

        expect($customRole->fresh()->permissions()->where('name', 'manage-gate-sync')->exists())->toBeTrue()
            ->and($targetUser->fresh()->permissions()->where('name', 'manage-gate-sync')->exists())->toBeTrue();
    }

    public function test_normal_admin_cannot_modify_own_direct_permissions_self_escalation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.permissions.update', $this->admin->id), [
            'permissions' => ['view-pharmacy-inventory'],
        ]);

        $response->assertForbidden();
        $this->admin->refresh();
        $this->assertFalse($this->admin->hasPermission('view-pharmacy-inventory'));
    }

    public function test_cannot_delete_protected_system_roles(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $this->actingAs($this->superAdmin)->delete(route('roles.destroy', $superAdminRole->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id]);

        $this->actingAs($this->superAdmin)->delete(route('roles.destroy', $adminRole->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_cannot_delete_role_assigned_to_active_users(): void
    {
        $medicalRole = Role::where('name', 'petugas_kesehatan')->first();
        $this->assertGreaterThan(0, $medicalRole->users()->count());

        $response = $this->actingAs($this->superAdmin)->delete(route('roles.destroy', $medicalRole->id));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $medicalRole->id]);
    }

    public function test_cannot_deactivate_or_strip_last_super_admin(): void
    {
        // $this->superAdmin is currently the only active super_admin
        $response = $this->actingAs($this->superAdmin)->post(route('users.toggle-status', $this->superAdmin->id));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->is_active);

        // Attempting to strip super_admin role from last super_admin
        $adminRole = Role::where('name', 'admin')->first();
        $response2 = $this->actingAs($this->superAdmin)->post(route('users.roles.update', $this->superAdmin->id), [
            'roles' => [$adminRole->id],
        ]);

        $response2->assertForbidden();
        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->isSuperAdmin());
    }
}
