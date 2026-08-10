<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Unauthenticated access to root redirects to login.
     */
    public function test_the_application_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated access to root returns a successful response.
     */
    public function test_the_application_returns_a_successful_response_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
