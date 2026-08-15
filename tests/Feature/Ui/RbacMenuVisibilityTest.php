<?php

namespace Tests\Feature\Ui;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_medical_user_sidebar_menu_visibility(): void
    {
        $user = User::where('email', 'fatimah.medis@sabira.test')->first();

        $response = $this->actingAs($user)->followingRedirects()->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Pelayanan Medis')
            ->assertSee('Kunjungan (Intake)')
            ->assertSee('Data Rekam Medis')
            ->assertSee('Dashboard Klinis')
            ->assertDontSee('Administrasi & Sistem', false)
            ->assertDontSee('Direktori Person')
            ->assertDontSee('Akun Pengguna')
            ->assertDontSee('Roles & Permissions', false)
            ->assertDontSee('Gate Sync Preview')
            ->assertDontSee('Log Audit System');
    }

    public function test_pharmacy_user_sidebar_menu_visibility(): void
    {
        $user = User::where('email', 'apoteker@sabira.test')->first();

        $response = $this->actingAs($user)->followingRedirects()->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Farmasi & Obat', false)
            ->assertSee('Dashboard Farmasi')
            ->assertSee('Master Data Obat')
            ->assertSee('Stok & Batch Obat', false)
            ->assertDontSee('Administrasi & Sistem', false)
            ->assertDontSee('Direktori Person')
            ->assertDontSee('Akun Pengguna')
            ->assertDontSee('Roles & Permissions', false)
            ->assertDontSee('Ruang Observasi')
            ->assertDontSee('Rujukan Eksternal');
    }

    public function test_operational_user_sidebar_menu_visibility(): void
    {
        $user = User::where('email', 'musyrif@sabira.test')->first();

        $response = $this->actingAs($user)->followingRedirects()->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Operasional Asrama')
            ->assertSee('Dashboard Operasional')
            ->assertSee('Handoff Asrama')
            ->assertDontSee('Administrasi & Sistem', false)
            ->assertDontSee('Farmasi & Obat', false)
            ->assertDontSee('Direktori Person');
    }

    public function test_management_user_sidebar_menu_visibility(): void
    {
        $user = User::where('email', 'pimpinan@sabira.test')->first();

        $response = $this->actingAs($user)->followingRedirects()->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Laporan & Manajemen', false)
            ->assertSee('Dashboard Manajemen')
            ->assertSee('Laporan Kesehatan')
            ->assertDontSee('Administrasi & Sistem', false)
            ->assertDontSee('Farmasi & Obat', false)
            ->assertDontSee('Ruang Observasi');
    }

    public function test_admin_user_sidebar_menu_visibility(): void
    {
        // Dedicated delegated admin (without clinical bypass)
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->sync([$adminRole->id]);

        $response = $this->actingAs($user)->followingRedirects()->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Administrasi & Sistem', false)
            ->assertSee('Direktori Person')
            ->assertSee('Akun Pengguna')
            ->assertSee('Roles & Permissions', false)
            ->assertSee('Mitra Faskes')
            ->assertSee('Gate Sync Preview')
            ->assertSee('Log Audit System');
    }
}
