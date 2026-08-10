<?php

use function Pest\Laravel\get;

test('liveness endpoint /health returns json status', function () {
    $response = get(route('health'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJsonStructure([
        'status',
        'app',
        'environment',
        'version',
        'timestamp',
    ]);
    $response->assertJson(['status' => 'ok']);
});

test('readiness endpoint /health/ready returns operational subsystem statuses without secrets', function () {
    $response = get(route('health.ready'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJsonStructure([
        'status',
        'timestamp',
        'dependencies' => [
            'database',
            'cache',
            'private_storage',
        ],
        'integrations' => [
            'gate' => [
                'driver',
                'sso_enabled',
                'sync_apply_enabled',
            ],
            'attendance' => [
                'driver',
                'enabled',
            ],
        ],
    ]);

    $data = $response->json();
    expect($data['status'])->toBe('ready');
    expect($data['dependencies']['database'])->toBe('connected');
    expect($data['dependencies']['cache'])->toBe('operational');
    expect($data['dependencies']['private_storage'])->toBe('writable');

    // Guarantee no secret keys or database connection passwords leaked in response
    $rawContent = $response->getContent();
    expect($rawContent)->not->toContain('password');
    expect($rawContent)->not->toContain('client_secret');
    expect($rawContent)->not->toContain('APP_KEY');
});
