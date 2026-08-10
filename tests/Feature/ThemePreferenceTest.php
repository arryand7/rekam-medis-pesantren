<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('theme switcher component and anti-flicker script are rendered in app layout for authenticated user', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('sabira_theme_preference');
    $response->assertSee('theme-switcher-component');
    $response->assertSee('Light');
    $response->assertSee('Dark');
    $response->assertSee('System');
});
