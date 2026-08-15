<?php

use App\Models\ApplicationIdentity;
use App\Models\User;
use App\Services\ApplicationIdentityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require_once dirname(__DIR__, 2).'/Support/BrandingTestHelpers.php';

test('fresh installation renders original default identity logo favicon title and footer', function () {
    $login = $this->get(route('login'));

    $login->assertOk()
        ->assertSee('SABIRA POSKESTREN Health')
        ->assertSee('Layanan Kesehatan Pesantren')
        ->assertSee('branding/default/logo-light.svg', false)
        ->assertSee('branding/default/favicon.svg', false);

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard', false)
        ->assertSee('SABIRA POSKESTREN Health')
        ->assertSee('branding/default/app-mark.svg', false);
});

test('configured identity renders globally in login header sidebar title and footer', function () {
    $manager = createBrandingManager();
    $this->actingAs($manager)->put(route('admin.system.application-identity.update'), brandingPayload())
        ->assertSessionHasNoErrors();

    auth()->logout();
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('SABIRA Health Test')
        ->assertSee('Sehat, Aman, Terlayani')
        ->assertSee('Institusi Sintetis');

    $this->actingAs($manager)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('SABIRA Health Test')
        ->assertSee('SAHAT')
        ->assertSee('Institusi Sintetis — Layanan Kesehatan');
});

test('dark logo favicon and missing custom files resolve through safe fallback chain', function () {
    Storage::fake('public');
    $manager = createBrandingManager();
    $this->actingAs($manager)->put(route('admin.system.application-identity.update'), [
        ...brandingPayload(),
        'logo' => UploadedFile::fake()->image('primary.png', 180, 80),
    ])->assertSessionHasNoErrors();

    $identity = app(ApplicationIdentityService::class)->get();
    expect($identity['logo_url'])->toContain('/storage/branding/');
    expect($identity['logo_dark_url'])->toContain('/storage/branding/');
    expect($identity['favicon_url'])->toContain('branding/default/favicon.svg');

    Storage::disk('public')->delete((string) ApplicationIdentity::query()->sole()->logo_path);
    app(ApplicationIdentityService::class)->forget();
    $fallback = app(ApplicationIdentityService::class)->get();
    expect($fallback['logo_url'])->toContain('branding/default/logo-light.svg');
    expect($fallback['logo_dark_url'])->toContain('branding/default/logo-dark.svg');
});

test('branding settings page exposes accessible sections previews upload guidance and reset confirmation', function () {
    $manager = createBrandingManager();

    $this->actingAs($manager)->get(route('admin.system.application-identity.edit'))
        ->assertOk()
        ->assertSee('Identitas Utama')
        ->assertSee('Deskripsi & Footer', false)
        ->assertSee('Logo & Ikon', false)
        ->assertSee('Preview')
        ->assertSee('Kembalikan ke Identitas Default')
        ->assertSee('SVG upload ditolak')
        ->assertSee('aria-describedby', false)
        ->assertSee('accept="image/png,image/jpeg,image/webp"', false);
});

test('default svg assets are public generic and contain no protected emblem labels', function () {
    foreach (['app-mark.svg', 'logo-light.svg', 'logo-dark.svg', 'favicon.svg'] as $asset) {
        $contents = file_get_contents(public_path('branding/default/'.$asset));
        expect($contents)->not->toBeFalse()
            ->toContain('<svg')
            ->not->toContain('Red Cross')
            ->not->toContain('Red Crescent')
            ->not->toContain('copyright');
    }
});
