<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAdministrationTest extends TestCase
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

        // Create a dedicated normal admin without super_admin role
        $this->admin = User::factory()->create(['is_active' => true]);
        $adminRole = Role::where('name', 'admin')->first();
        $this->admin->roles()->sync([$adminRole->id]);
    }

    public function test_super_admin_can_view_role_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('roles.index'));

        $response->assertOk()
            ->assertViewIs('pages.roles.index')
            ->assertSee('Manajemen Role')
            ->assertSee('super_admin')
            ->assertSee('admin')
            ->assertSee('petugas_kesehatan');
    }

    public function test_super_admin_can_create_new_role_with_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('roles.store'), [
            'name' => 'staf_laboratorium',
            'display_name' => 'Staf Laboratorium',
            'description' => 'Petugas pemeriksa spesimen lab santri',
            'permissions' => ['view-patients', 'view-medical-visits'],
        ]);

        $role = Role::where('name', 'staf_laboratorium')->first();
        $this->assertNotNull($role);
        $this->assertEquals('Staf Laboratorium', $role->display_name);
        $this->assertTrue($role->hasPermission('view-patients'));
        $this->assertTrue($role->hasPermission('view-medical-visits'));
        $this->assertFalse($role->hasPermission('manage-users'));

        $response->assertRedirect(route('roles.show', $role->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ROLE_CREATED',
            'subject_id' => $role->id,
        ]);
    }

    public function test_super_admin_can_update_role_matrix(): void
    {
        $role = Role::create([
            'name' => 'custom_role',
            'display_name' => 'Custom Role Display',
        ]);
        $role->permissions()->sync([
            Permission::where('name', 'view-patients')->first()->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('roles.update', $role->id), [
            'name' => 'custom_role_updated',
            'display_name' => 'Custom Role Renamed',
            'description' => 'Updated description',
            'permissions' => ['view-patients', 'create-medical-visits'],
        ]);

        $role->refresh();
        $this->assertEquals('custom_role_updated', $role->name);
        $this->assertEquals('Custom Role Renamed', $role->display_name);
        $this->assertTrue($role->hasPermission('create-medical-visits'));

        $response->assertRedirect(route('roles.show', $role->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ROLE_UPDATED',
            'subject_id' => $role->id,
        ]);
    }

    public function test_super_admin_can_delete_unused_custom_role(): void
    {
        $role = Role::create([
            'name' => 'temporary_role',
            'display_name' => 'Temporary Role',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('roles.destroy', $role->id));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ROLE_DELETED',
            'subject_id' => $role->id,
        ]);
    }

    public function test_super_admin_can_view_user_index_and_detail(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $response->assertOk()
            ->assertViewIs('pages.users.index')
            ->assertSee($this->medicalUser->email);

        $detailResponse = $this->actingAs($this->superAdmin)->get(route('users.show', $this->medicalUser->id));
        $detailResponse->assertOk()
            ->assertViewIs('pages.users.show')
            ->assertSee($this->medicalUser->name)
            ->assertSee('Pratinjau Hak Akses Efektif');
    }

    public function test_super_admin_can_update_user_roles(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);
        $pharmacyRole = Role::where('name', 'farmasi')->first();

        $response = $this->actingAs($this->superAdmin)->post(route('users.roles.update', $targetUser->id), [
            'roles' => [$pharmacyRole->id],
        ]);

        $targetUser->refresh();
        $this->assertTrue($targetUser->roles->contains('name', 'farmasi'));

        $response->assertRedirect(route('users.show', $targetUser->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'USER_ROLE_ASSIGNED',
            'subject_id' => $targetUser->id,
        ]);
    }

    public function test_super_admin_can_assign_direct_permissions_to_user(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);
        $this->assertFalse($targetUser->hasPermission('view-pharmacy-inventory'));

        $response = $this->actingAs($this->superAdmin)->post(route('users.permissions.update', $targetUser->id), [
            'permissions' => ['view-pharmacy-inventory'],
        ]);

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasPermission('view-pharmacy-inventory'));

        $effective = $targetUser->getEffectivePermissionsWithSource();
        $this->assertArrayHasKey('view-pharmacy-inventory', $effective);
        $this->assertEquals('DIRECT USER', $effective['view-pharmacy-inventory']['source']);

        $response->assertRedirect(route('users.show', $targetUser->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'USER_PERMISSION_GRANTED',
            'subject_id' => $targetUser->id,
        ]);
    }

    public function test_super_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.toggle-status', $targetUser->id));

        $targetUser->refresh();
        $this->assertFalse($targetUser->is_active);

        $response->assertRedirect(route('users.show', $targetUser->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'USER_STATUS_TOGGLED',
            'subject_id' => $targetUser->id,
        ]);
    }
}
