<?php

use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\Gate\FakeGateOidcClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    FakeGateOidcClient::reset();
});

test('guest accessing root URL is redirected to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('guest accessing dashboard URL is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('guest accessing sensitive clinical routes is denied and redirected to login', function () {
    $this->get('/patients')->assertRedirect(route('login'));
    $this->get('/visits')->assertRedirect(route('login'));
    $this->get('/visits/create')->assertRedirect(route('login'));
    $this->get('/observations')->assertRedirect(route('login'));
    $this->get('/pharmacy/inventory')->assertRedirect(route('login'));
    $this->get('/pharmacy/medicines')->assertRedirect(route('login'));
    $this->get('/consultations')->assertRedirect(route('login'));
    $this->get('/referrals')->assertRedirect(route('login'));
    $this->get('/discharges')->assertRedirect(route('login'));
    $this->get('/reports')->assertRedirect(route('login'));
});

test('guest accessing sensitive administrative routes is denied and redirected to login', function () {
    $this->get('/users')->assertRedirect(route('login'));
    $this->get('/roles')->assertRedirect(route('login'));
    $this->get('/people')->assertRedirect(route('login'));
    $this->get('/audit-logs')->assertRedirect(route('login'));
    $this->get('/gate-sync/preview')->assertRedirect(route('login'));
    $this->get('/gate-sync/conflicts')->assertRedirect(route('login'));
    $this->get('/integration/outbox')->assertRedirect(route('login'));
});

test('guest cannot access patient medical profile directly', function () {
    $patient = Patient::factory()->create();

    $this->get("/patients/{$patient->id}")->assertRedirect(route('login'));
});

test('authenticated medical staff is routed to clinical dashboard', function () {
    $medicalRole = Role::firstOrCreate(['name' => 'petugas_kesehatan'], ['display_name' => 'Petugas Medis']);
    $perm = Permission::firstOrCreate(['name' => 'view-clinical-dashboard'], ['display_name' => 'View Clinical Dashboard']);
    $medicalRole->permissions()->syncWithoutDetaching([$perm->id]);

    $doctor = User::factory()->create();
    $doctor->roles()->attach($medicalRole->id);

    actingAs($doctor)->get('/')->assertRedirect(route('dashboards.clinical'));
    actingAs($doctor)->get('/dashboard')->assertRedirect(route('dashboards.clinical'));
});

test('authenticated dorm staff is routed to operational dashboard', function () {
    $dormRole = Role::firstOrCreate(['name' => 'pembina_asrama'], ['display_name' => 'Pembina Asrama']);
    $perm = Permission::firstOrCreate(['name' => 'view-operational-dashboard'], ['display_name' => 'View Operational Dashboard']);
    $dormRole->permissions()->syncWithoutDetaching([$perm->id]);

    $ustadz = User::factory()->create();
    $ustadz->roles()->attach($dormRole->id);

    actingAs($ustadz)->get('/')->assertRedirect(route('dashboards.operational'));
    actingAs($ustadz)->get('/dashboard')->assertRedirect(route('dashboards.operational'));
});

test('authenticated leadership is routed to management dashboard', function () {
    $leadRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan Pesantren']);
    $perm = Permission::firstOrCreate(['name' => 'view-management-dashboard'], ['display_name' => 'View Management Dashboard']);
    $leadRole->permissions()->syncWithoutDetaching([$perm->id]);

    $mudir = User::factory()->create();
    $mudir->roles()->attach($leadRole->id);

    actingAs($mudir)->get('/')->assertRedirect(route('dashboards.management'));
    actingAs($mudir)->get('/dashboard')->assertRedirect(route('dashboards.management'));
});

test('authenticated technical admin accesses admin dashboard without auto-escalating to clinical records', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $perm = Permission::firstOrCreate(['name' => 'manage-users'], ['display_name' => 'Manage Users']);
    $adminRole->permissions()->syncWithoutDetaching([$perm->id]);

    $admin = User::factory()->create();
    $admin->roles()->attach($adminRole->id);

    // Admin hits /dashboard and gets admin view (200)
    $response = actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);

    // Admin without explicit clinical permission cannot access clinical dashboard
    actingAs($admin)->get('/dashboards/clinical')->assertForbidden();
});

test('dorm staff without clinical permission cannot access clinical dashboard', function () {
    $dormRole = Role::firstOrCreate(['name' => 'pembina_asrama_only'], ['display_name' => 'Pembina Asrama']);
    $perm = Permission::firstOrCreate(['name' => 'view-operational-dashboard'], ['display_name' => 'View Operational Dashboard']);
    $dormRole->permissions()->syncWithoutDetaching([$perm->id]);

    $ustadz = User::factory()->create();
    $ustadz->roles()->attach($dormRole->id);

    actingAs($ustadz)->get('/dashboards/clinical')->assertForbidden();
});

test('login endpoint does not auto-authenticate guest', function () {
    Config::set('gate.sso_enabled', false);

    $response = $this->get('/login');
    $response->assertStatus(200);
    expect(Auth::check())->toBeFalse();
});

test('valid Gate OIDC callback authenticates user and creates session', function () {
    Config::set('gate.sso_enabled', true);
    Config::set('gate.driver', 'fake');

    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-RUNTIME-001',
        name: 'dr. H. Zulkifli Medis',
        email: 'zulkifli@sabira.id',
        userType: 'tenaga_kesehatan',
        sourceStatus: 'active',
        appRoles: ['tenaga_kesehatan']
    );

    FakeGateOidcClient::addMockUser($userDTO);
    session(['gate_auth_state' => 'runtime_valid_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=runtime_valid_state');
    $response->assertRedirect(route('dashboard'));

    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->email)->toBe('zulkifli@sabira.id');
});

test('invalid state in Gate callback is rejected and leaves user unauthenticated', function () {
    session(['gate_auth_state' => 'expected_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=forged_state');
    $response->assertRedirect(route('login'));
    expect(Auth::check())->toBeFalse();
});

test('user denied application entitlement is blocked from accessing application', function () {
    Config::set('gate.sso_enabled', true);
    Config::set('gate.driver', 'fake');

    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-UNAUTHORIZED-99',
        name: 'Akun Belum Diberi Izin',
        email: 'unauthorized@sabira.id',
        userType: 'guest',
        sourceStatus: 'active',
        appRoles: []
    );

    $entitlement = new GateApplicationEntitlementDTO(
        gateUserId: 'GATE-USR-UNAUTHORIZED-99',
        appCode: 'poskestren-health',
        status: 'not_assigned',
        roles: []
    );

    FakeGateOidcClient::addMockUser($userDTO, $entitlement);
    session(['gate_auth_state' => 'unauthorized_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=unauthorized_state');
    $response->assertRedirect(route('auth.gate.access_denied'));
    expect(Auth::check())->toBeFalse();
});

test('logout properly invalidates session and redirects to login', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/logout');

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');
    expect($targetUrl)->toContain('login');
    expect(Auth::check())->toBeFalse();

    // Re-visiting dashboard after logout must redirect to login
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('guest posting to logout redirects safely without exception', function () {
    $response = $this->post('/logout');

    $response->assertRedirect();
    expect(Auth::check())->toBeFalse();
});

test('get request to logout is rejected with 405 method not allowed', function () {
    $response = $this->get('/logout');

    $response->assertStatus(405);
});

test('gate before only allows exact local permission and defers to model policy otherwise', function () {
    $adminRole = Role::firstOrCreate(['name' => 'sysadmin_role'], ['display_name' => 'System Admin']);
    $perm = Permission::firstOrCreate(['name' => 'manage-users'], ['display_name' => 'Manage Users']);
    $adminRole->permissions()->syncWithoutDetaching([$perm->id]);

    $admin = User::factory()->create();
    $admin->roles()->attach($adminRole->id);

    // Exact permission check passes
    expect(Gate::forUser($admin)->allows('manage-users'))->toBeTrue();

    // Ability without permission returns false and is not granted
    expect(Gate::forUser($admin)->allows('view-patients'))->toBeFalse();
    expect(Gate::forUser($admin)->allows('view-clinical-dashboard'))->toBeFalse();
    expect(Gate::forUser($admin)->allows('non_existent_ability'))->toBeFalse();

    // Model policy evaluation: PatientPolicy view requires 'view-patients'
    $patient = Patient::factory()->create();
    expect(Gate::forUser($admin)->allows('view', $patient))->toBeFalse();
});

test('user can authenticate directly using valid email and password', function () {
    $user = User::factory()->create([
        'email' => 'dokter.poskestren@sabira.test',
        'password' => bcrypt('rahasia123'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'dokter.poskestren@sabira.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('dashboard'));
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

test('user can authenticate directly using username/name and password', function () {
    $user = User::factory()->create([
        'name' => 'petugas_farmasi_01',
        'email' => 'farmasi@sabira.test',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'petugas_farmasi_01',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

test('user can authenticate directly using person nis_nip and password', function () {
    $person = Person::factory()->create([
        'nis_nip' => 'NIP-2026-999',
        'name' => 'Ustadz Ahmad Pengasuh',
    ]);

    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'ahmad.pengasuh@sabira.test',
        'password' => bcrypt('ponpes2026'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'NIP-2026-999',
        'password' => 'ponpes2026',
    ]);

    $response->assertRedirect(route('dashboard'));
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

test('user cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'staf@sabira.test',
        'password' => bcrypt('correct_password'),
        'is_active' => true,
    ]);

    $response = $this->from('/login')->post('/login', [
        'login' => 'staf@sabira.test',
        'password' => 'wrong_password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('login');
    expect(Auth::check())->toBeFalse();
});

test('inactive user cannot authenticate directly', function () {
    $user = User::factory()->create([
        'email' => 'inactive@sabira.test',
        'password' => bcrypt('password123'),
        'is_active' => false,
    ]);

    $response = $this->from('/login')->post('/login', [
        'login' => 'inactive@sabira.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('login');
    expect(Auth::check())->toBeFalse();
});
