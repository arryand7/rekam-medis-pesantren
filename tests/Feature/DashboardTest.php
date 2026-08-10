<?php

use App\Models\User;

test('dashboard shell requires authentication and redirects unauthenticated guests to login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('dashboard shell renders successfully for authenticated user with POSKESTREN branding', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('SABIRA POSKESTREN');
    $response->assertSee('Dashboard Pelayanan Poskestren');
    $response->assertSee('Santri yang sakit tidak boleh tetap berada di asrama');
});
