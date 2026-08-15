<?php

use App\Contracts\GateOidcClientContract;
use App\DTOs\GateOidcTokenResponseDTO;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Route;

test('audit payload sanitization is recursive for authentication secrets', function () {
    AuditLogService::log(
        action: 'security.sanitization_test',
        subjectType: 'SecurityTest',
        after: [
            'access_token' => 'top-secret-token',
            'nested' => [
                'client_secret' => 'top-secret-client',
                'state' => 'top-secret-state',
            ],
        ]
    );

    $payload = AuditLog::where('action', 'security.sanitization_test')->firstOrFail()->payload_after;

    expect($payload['access_token'])->toBe('********')
        ->and($payload['nested']['client_secret'])->toBe('********')
        ->and($payload['nested']['state'])->toBe('********')
        ->and(json_encode($payload))->not->toContain('top-secret');
});

test('gate userinfo failure is controlled and does not leak provider exception details', function () {
    $client = Mockery::mock(GateOidcClientContract::class);
    $client->shouldReceive('exchangeAuthorizationCode')->once()->andReturn(new GateOidcTokenResponseDTO(
        accessToken: 'secret-access-token',
        idToken: 'secret-id-token',
        tokenType: 'Bearer',
        expiresIn: 3600,
    ));
    $client->shouldReceive('fetchUserInfo')->once()->andThrow(
        new RuntimeException('provider secret at /machine/private/path')
    );

    $this->app->instance(GateOidcClientContract::class, $client);
    session(['gate_auth_state' => 'valid-state']);

    $response = $this->get('/auth/gate/callback?code=opaque-code&state=valid-state');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Gate tidak dapat memvalidasi identitas atau hak akses aplikasi.');

    $audit = AuditLog::where('action', 'gate_login.failed')->latest('created_at')->firstOrFail();
    expect(json_encode($audit->toArray()))
        ->not->toContain('provider secret')
        ->not->toContain('/machine/private/path')
        ->not->toContain('secret-access-token');
});

test('debug disabled error responses do not expose stack traces sql or local paths', function () {
    config()->set('app.debug', false);

    Route::get('/__phase5d/forced-error', function () {
        throw new RuntimeException('SQLSTATE secret /Users/example/private.php');
    });

    $response = $this->get('/__phase5d/forced-error');

    $response->assertStatus(500);
    expect($response->getContent())
        ->not->toContain('SQLSTATE')
        ->not->toContain('/Users/example')
        ->not->toContain('RuntimeException')
        ->not->toContain('Stack trace');
});

test('health endpoints expose no configured secrets or clinical fields', function () {
    config()->set('gate.client_secret', 'phase5d-gate-secret');
    config()->set('integration.attendance.api_key', 'phase5d-attendance-secret');

    foreach (['/health', '/health/ready', '/up'] as $path) {
        $response = $this->get($path);
        $content = $response->getContent();

        expect($content)
            ->not->toContain('phase5d-gate-secret')
            ->not->toContain('phase5d-attendance-secret')
            ->not->toContain('diagnosis')
            ->not->toContain('medications');
    }
});
