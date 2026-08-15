<?php

use App\Models\ApplicationIdentity;
use App\Models\AuditLog;
use App\Services\ApplicationIdentityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require_once dirname(__DIR__, 2).'/Support/BrandingTestHelpers.php';

test('authorized system administrator can update identity and invalidate cached rendering', function () {
    $user = createBrandingManager();
    $service = app(ApplicationIdentityService::class);

    expect($service->get()['application_name'])->toBe('SABIRA POSKESTREN Health');

    $response = $this->actingAs($user)->put(route('admin.system.application-identity.update'), brandingPayload());

    $response->assertRedirect(route('admin.system.application-identity.edit'));
    expect(ApplicationIdentity::query()->sole()->application_name)->toBe('SABIRA Health Test');
    expect($service->get()['application_name'])->toBe('SABIRA Health Test');
    expect(AuditLog::where('action', 'APPLICATION_IDENTITY_UPDATED')->exists())->toBeTrue();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('SABIRA Health Test')
        ->assertSee('Institusi Sintetis');
});

test('valid raster uploads use collision safe names and emit asset audit events', function (string $field, string $extension, string $auditAction) {
    Storage::fake('public');
    $user = createBrandingManager();
    $file = UploadedFile::fake()->image("identity.{$extension}", 160, 160)->size(128);

    $response = $this->actingAs($user)->put(
        route('admin.system.application-identity.update'),
        [...brandingPayload(), $field => $file]
    );

    $response->assertSessionHasNoErrors();
    $column = match ($field) {
        'logo' => 'logo_path',
        'logo_dark' => 'logo_dark_path',
        default => 'favicon_path',
    };
    $path = ApplicationIdentity::query()->sole()->{$column};

    expect($path)->toStartWith('branding/')
        ->not->toContain('identity');
    Storage::disk('public')->assertExists($path);
    expect(AuditLog::where('action', $auditAction)->exists())->toBeTrue();
})->with([
    'PNG primary logo' => ['logo', 'png', 'APPLICATION_LOGO_UPDATED'],
    'JPEG dark logo' => ['logo_dark', 'jpg', 'APPLICATION_LOGO_UPDATED'],
    'WebP favicon' => ['favicon', 'webp', 'APPLICATION_FAVICON_UPDATED'],
]);

test('replacing a custom logo removes the old asset without touching defaults', function () {
    Storage::fake('public');
    $user = createBrandingManager();

    $this->actingAs($user)->put(route('admin.system.application-identity.update'), [
        ...brandingPayload(),
        'logo' => UploadedFile::fake()->image('first.png', 120, 120),
    ])->assertSessionHasNoErrors();
    $oldPath = ApplicationIdentity::query()->sole()->logo_path;

    $this->put(route('admin.system.application-identity.update'), [
        ...brandingPayload(),
        'logo' => UploadedFile::fake()->image('second.png', 120, 120),
    ])->assertSessionHasNoErrors();
    $newPath = ApplicationIdentity::query()->sole()->logo_path;

    expect($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
    expect(public_path('branding/default/logo-light.svg'))->toBeFile();
});

test('reset restores source defaults clears cache deletes custom assets and audits action', function () {
    Storage::fake('public');
    $user = createBrandingManager();

    $this->actingAs($user)->put(route('admin.system.application-identity.update'), [
        ...brandingPayload(),
        'logo' => UploadedFile::fake()->image('custom.png', 120, 120),
        'favicon' => UploadedFile::fake()->image('icon.png', 64, 64),
    ])->assertSessionHasNoErrors();
    $identity = ApplicationIdentity::query()->sole();
    $paths = [$identity->logo_path, $identity->favicon_path];

    $this->post(route('admin.system.application-identity.reset'), ['confirm_reset' => '1'])
        ->assertRedirect(route('admin.system.application-identity.edit'));

    expect(ApplicationIdentity::count())->toBe(0);
    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
    expect(app(ApplicationIdentityService::class)->get()['application_name'])->toBe('SABIRA POSKESTREN Health');
    expect(AuditLog::where('action', 'APPLICATION_IDENTITY_RESET')->exists())->toBeTrue();
});

test('reset requires an explicit confirmation', function () {
    $user = createBrandingManager();

    $this->actingAs($user)->post(route('admin.system.application-identity.reset'))
        ->assertSessionHasErrors('confirm_reset');
});
