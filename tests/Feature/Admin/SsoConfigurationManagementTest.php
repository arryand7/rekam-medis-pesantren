<?php

use App\Contracts\GateOidcClientContract;
use App\Models\AuditLog;
use App\Models\SsoConfiguration;
use App\Services\SsoConfigurationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

require_once dirname(__DIR__, 2).'/Support/BrandingTestHelpers.php';
require_once dirname(__DIR__, 2).'/Support/SsoConfigurationTestHelpers.php';

test('super admin can persist SSO settings and invalidate cached runtime configuration', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');
    $service = app(SsoConfigurationService::class);

    expect($service->get()['sso_enabled'])->toBeFalse()
        ->and($service->get()['driver'])->toBe('fake');

    $response = $this->actingAs($superAdmin)->put(
        route('admin.system.sso-configuration.update'),
        ssoConfigurationPayload()
    );

    $response->assertRedirect(route('admin.system.sso-configuration.edit'))
        ->assertSessionHasNoErrors();

    $stored = SsoConfiguration::query()->sole();
    expect($stored->sso_enabled)->toBeTrue()
        ->and($stored->driver)->toBe('http')
        ->and($stored->client_secret)->toBe('synthetic-sso-secret-value-123456')
        ->and($stored->getRawOriginal('client_secret'))->not->toContain('synthetic-sso-secret-value-123456');

    $runtime = $service->get();
    expect($runtime['base_url'])->toBe('https://gate.sabira.test')
        ->and($runtime['client_secret_configured'])->toBeTrue()
        ->and($runtime['is_ready'])->toBeTrue();

    expect(AuditLog::where('action', 'SSO_CONFIGURATION_UPDATED')->exists())->toBeTrue()
        ->and(AuditLog::where('action', 'SSO_CLIENT_SECRET_ROTATED')->exists())->toBeTrue();

    $serializedCache = json_encode(Cache::get(config('gate.settings_cache_key')), JSON_THROW_ON_ERROR);
    expect($serializedCache)->not->toContain('synthetic-sso-secret-value-123456');
});

test('blank secret retains encrypted credential while explicit value rotates it', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');

    $this->actingAs($superAdmin)
        ->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())
        ->assertSessionHasNoErrors();

    $firstCiphertext = SsoConfiguration::query()->sole()->getRawOriginal('client_secret');

    $this->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload([
        'base_url' => 'https://gate-updated.sabira.test',
        'client_secret' => '',
    ]))->assertSessionHasNoErrors();

    $configuration = SsoConfiguration::query()->sole();
    expect($configuration->client_secret)->toBe('synthetic-sso-secret-value-123456')
        ->and($configuration->getRawOriginal('client_secret'))->toBe($firstCiphertext)
        ->and(AuditLog::where('action', 'SSO_CLIENT_SECRET_ROTATED')->count())->toBe(1);

    $this->get(route('admin.system.sso-configuration.edit'))
        ->assertOk()
        ->assertSee('Secret terenkripsi sudah tersedia')
        ->assertDontSee('synthetic-sso-secret-value-123456');
});

test('SSO activation is fail closed when security requirements are incomplete', function (array $overrides, string $field) {
    $superAdmin = createBrandingUserWithRole('super_admin');

    $this->actingAs($superAdmin)
        ->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(SsoConfiguration::query()->count())->toBe(0);
})->with([
    'fake driver' => [['driver' => 'fake'], 'driver'],
    'placeholder endpoint' => [['base_url' => 'https://gate.example.invalid'], 'base_url'],
    'insecure non-local endpoint' => [['base_url' => 'http://gate.sabira.test'], 'base_url'],
    'missing client id' => [['client_id' => ''], 'client_id'],
    'missing secret' => [['client_secret' => ''], 'client_secret'],
    'missing openid scope' => [['scopes' => 'profile email'], 'scopes'],
    'wrong callback path' => [['redirect_uri' => 'https://health.sabira.test/oauth/callback'], 'redirect_uri'],
]);

test('reset removes persistent credential clears cache and disables SSO', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');
    $service = app(SsoConfigurationService::class);

    $this->actingAs($superAdmin)
        ->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())
        ->assertSessionHasNoErrors();
    expect($service->get()['sso_enabled'])->toBeTrue();

    $this->post(route('admin.system.sso-configuration.reset'), ['confirm_reset' => '1'])
        ->assertRedirect(route('admin.system.sso-configuration.edit'));

    expect(SsoConfiguration::query()->count())->toBe(0)
        ->and($service->get()['sso_enabled'])->toBeFalse()
        ->and($service->get()['driver'])->toBe('fake')
        ->and(AuditLog::where('action', 'SSO_CONFIGURATION_RESET')->exists())->toBeTrue();
});

test('login UI and redirect are controlled by persistent SSO readiness', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('Masuk dengan Akun SABIRA');
    session(['gate_auth_state' => 'disabled-state']);
    $this->get(route('auth.gate.callback', ['code' => 'code', 'state' => 'disabled-state']))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');
    $this->get(route('login', ['redirect' => 1]))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    $this->actingAs($superAdmin)
        ->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())
        ->assertSessionHasNoErrors();
    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk dengan Akun SABIRA');

    $redirect = $this->get(route('login', ['redirect' => 1]));
    $redirect->assertRedirect();
    expect($redirect->headers->get('Location'))
        ->toStartWith('https://gate.sabira.test/oauth/authorize')
        ->toContain('client_id=poskestren-health-test');
});

test('reset requires explicit confirmation', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');

    $this->actingAs($superAdmin)
        ->post(route('admin.system.sso-configuration.reset'))
        ->assertSessionHasErrors('confirm_reset');
});

test('HTTP OIDC client reads the decrypted persistent secret without exposing it to audit', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');

    $this->actingAs($superAdmin)
        ->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())
        ->assertSessionHasNoErrors();

    Http::fake([
        'https://gate.sabira.test/oauth/token' => Http::response([
            'access_token' => 'synthetic-access-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]),
    ]);

    app(GateOidcClientContract::class)->exchangeAuthorizationCode('synthetic-code');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://gate.sabira.test/oauth/token'
        && $request['client_secret'] === 'synthetic-sso-secret-value-123456'
        && $request['client_id'] === 'poskestren-health-test');

    expect(AuditLog::query()->get()->toJson())->not->toContain('synthetic-sso-secret-value-123456');
});
