<?php

use function Pest\Laravel\get;

test('health endpoint returns json status', function () {
    $response = get(route('health'));

    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJsonStructure([
        'status',
        'app',
        'timestamp',
        'timezone',
        'checks' => [
            'database',
            'cache',
            'storage',
        ],
    ]);
});
