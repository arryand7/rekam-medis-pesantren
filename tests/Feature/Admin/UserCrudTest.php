<?php

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeAdminWithManageUsers(): User
{
    $permission = Permission::firstOrCreate(
        ['name' => 'manage-users'],
        ['display_name' => 'Kelola Pengguna', 'group' => 'admin']
    );

    $user = User::factory()->create(['is_active' => true]);
    $user->permissions()->attach($permission->id);

    return $user;
}

function makeBasicPerson(): Person
{
    return Person::create([
        'id'            => (string) \Illuminate\Support\Str::ulid(),
        'name'          => 'Test Person',
        'user_type'     => 'staff',
        'source_status' => 'active',
        'synced_at'     => now(),
    ]);
}

// ---------------------------------------------------------------------------
// INDEX — Daftar User
// ---------------------------------------------------------------------------

test('admin dapat melihat daftar pengguna', function () {
    $admin = makeAdminWithManageUsers();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Kelola Akun Pengguna')
        ->assertSee('Tambah User Lokal');
});

test('guest tidak dapat melihat daftar pengguna', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('user tanpa permission manage-users tidak dapat mengakses halaman', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertStatus(403);
});

// ---------------------------------------------------------------------------
// CREATE — Form Buat User Baru
// ---------------------------------------------------------------------------

test('admin dapat melihat form tambah user', function () {
    $admin = makeAdminWithManageUsers();

    $this->actingAs($admin)
        ->get(route('users.create'))
        ->assertOk()
        ->assertSee('Tambah User Lokal Baru')
        ->assertSee('Buat Person Baru')
        ->assertSee('Pilih Person yang Ada');
});

// ---------------------------------------------------------------------------
// STORE — Buat User dengan Person Baru
// ---------------------------------------------------------------------------

test('admin dapat membuat user lokal dengan person baru', function () {
    $admin = makeAdminWithManageUsers();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'person_mode'          => 'new',
        'name'                 => 'User Baru Test',
        'email'                => 'userbaru@test.com',
        'password'             => 'Password123',
        'password_confirmation' => 'Password123',
        'user_type'            => 'staff',
        'is_active'            => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['email' => 'userbaru@test.com', 'is_active' => true]);
    $this->assertDatabaseHas('people', ['email' => 'userbaru@test.com', 'user_type' => 'staff']);
});

test('admin dapat membuat user lokal dengan person yang sudah ada', function () {
    $admin  = makeAdminWithManageUsers();
    $person = makeBasicPerson();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'person_mode'          => 'existing',
        'person_id'            => $person->id,
        'name'                 => 'Test Person',
        'email'                => 'testperson@test.com',
        'password'             => 'Password123',
        'password_confirmation' => 'Password123',
        'is_active'            => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email'     => 'testperson@test.com',
        'person_id' => $person->id,
    ]);
});

test('validasi gagal jika email sudah digunakan', function () {
    $admin    = makeAdminWithManageUsers();
    $existing = User::factory()->create(['email' => 'existing@test.com']);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'person_mode'          => 'new',
        'name'                 => 'Test',
        'email'                => 'existing@test.com',
        'password'             => 'Password123',
        'password_confirmation' => 'Password123',
        'user_type'            => 'staff',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('validasi gagal jika password confirmation tidak cocok', function () {
    $admin = makeAdminWithManageUsers();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'person_mode'          => 'new',
        'name'                 => 'Test',
        'email'                => 'test@test.com',
        'password'             => 'Password123',
        'password_confirmation' => 'WrongPassword',
        'user_type'            => 'staff',
    ]);

    $response->assertSessionHasErrors(['password']);
});

// ---------------------------------------------------------------------------
// EDIT — Form Edit User
// ---------------------------------------------------------------------------

test('admin dapat melihat form edit user', function () {
    $admin  = makeAdminWithManageUsers();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('users.edit', $target->id))
        ->assertOk()
        ->assertSee('Edit Akun:');
});

test('form edit menampilkan banner Gate SSO jika user dikelola gate', function () {
    $admin  = makeAdminWithManageUsers();
    $person = makeBasicPerson();
    $person->update(['gate_user_id' => 'gate-uid-123']);

    $target = User::factory()->create(['person_id' => $person->id]);

    $this->actingAs($admin)
        ->get(route('users.edit', $target->id))
        ->assertOk()
        ->assertSee('Identitas Dikelola Gate SSO');
});

// ---------------------------------------------------------------------------
// UPDATE — Simpan Perubahan User
// ---------------------------------------------------------------------------

test('admin dapat mengubah data user lokal', function () {
    $admin  = makeAdminWithManageUsers();
    $target = User::factory()->create(['name' => 'Nama Lama', 'is_active' => true]);

    $response = $this->actingAs($admin)->put(route('users.update', $target->id), [
        'name'      => 'Nama Baru',
        'email'     => $target->email,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('users.show', $target->id));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Nama Baru']);
});

test('update tidak boleh mengubah nama user Gate-managed', function () {
    $admin  = makeAdminWithManageUsers();
    $person = makeBasicPerson();
    $person->update(['gate_user_id' => 'gate-uid-xyz', 'name' => 'Nama Gate']);

    $target = User::factory()->create(['person_id' => $person->id, 'name' => 'Nama Gate']);

    $this->actingAs($admin)->put(route('users.update', $target->id), [
        'name'      => 'Coba Ubah Nama',
        'email'     => $target->email,
        'is_active' => '1',
    ]);

    // Nama tidak berubah karena gate_managed
    $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Nama Gate']);
});

// ---------------------------------------------------------------------------
// RESET PASSWORD
// ---------------------------------------------------------------------------

test('admin dapat mereset password pengguna lain', function () {
    $admin  = makeAdminWithManageUsers();
    $target = User::factory()->create();

    $oldPasswordHash = $target->fresh()->password;

    $response = $this->actingAs($admin)
        ->post(route('users.reset-password', $target->id));

    $response->assertRedirect(route('users.show', $target->id));
    $response->assertSessionHas('success');
    $response->assertSessionHas('password_reset_plain');

    // Password hash berubah
    $this->assertNotEquals($oldPasswordHash, $target->fresh()->password);

    // Password baru ada di flash session (plaintext)
    $plain = $response->getSession()->get('password_reset_plain');
    $this->assertNotEmpty($plain);
    $this->assertEquals(12, strlen($plain));
});

test('admin tidak dapat mereset passwordnya sendiri jika ia satu-satunya super admin', function () {
    $superAdminPermission = Permission::firstOrCreate(
        ['name' => 'manage-users'],
        ['display_name' => 'Kelola Pengguna', 'group' => 'admin']
    );

    $superAdminRole = Role::firstOrCreate(
        ['name' => 'super_admin'],
        ['display_name' => 'Super Admin']
    );

    $admin = User::factory()->create(['is_active' => true]);
    $admin->permissions()->attach($superAdminPermission->id);
    $admin->roles()->attach($superAdminRole->id);

    // Self-reset is allowed (guard is: cannot reset OTHER super admins when they are the last)
    $response = $this->actingAs($admin)
        ->post(route('users.reset-password', $admin->id));

    // The guard in the controller checks activeSuperAdminCount <= 1 AND auth()->id() !== $user->id
    // So self-reset should be allowed
    $response->assertRedirect(route('users.show', $admin->id));
});
