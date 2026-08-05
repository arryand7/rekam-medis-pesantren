<?php

use function Pest\Laravel\get;

test('theme switcher component and anti-flicker script are rendered in app layout', function () {
    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('sabira_theme_preference');
    $response->assertSee('theme-switcher-component');
    $response->assertSee('Light');
    $response->assertSee('Dark');
    $response->assertSee('System');
});
