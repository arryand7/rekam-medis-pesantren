<?php

use function Pest\Laravel\get;

test('dashboard shell renders successfully with POSKESTREN branding', function () {
    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('SABIRA POSKESTREN');
    $response->assertSee('Dashboard Pelayanan Poskestren');
    $response->assertSee('Santri yang sakit tidak boleh tetap berada di asrama');
});
