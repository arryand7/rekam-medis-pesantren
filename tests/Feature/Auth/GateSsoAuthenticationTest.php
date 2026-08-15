<?php

use App\DTOs\GateUserInfoDTO;
use App\Models\User;
use App\Services\Gate\FakeGateOidcClient;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    FakeGateOidcClient::reset();
});

test('login page redirects to Gate authorization endpoint with state and nonce when redirect query is present', function () {
    $response = $this->get('/login?redirect=1');

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');

    expect($targetUrl)->toContain('https://gate.example.invalid/oauth/authorize');
    expect($targetUrl)->toContain('client_id=');
    expect($targetUrl)->toContain('state=');
    expect(session()->has('gate_auth_state'))->toBeTrue();
    expect(session()->has('gate_auth_nonce'))->toBeTrue();
});

test('callback with mismatched or missing state is rejected (CSRF/replay protection)', function () {
    session(['gate_auth_state' => 'valid_session_state']);

    $response = $this->get('/auth/gate/callback?code=test_auth_code&state=forged_state');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error');
    expect(Auth::check())->toBeFalse();
});

test('callback with token exchange failure redirects to login with sanitized error', function () {
    FakeGateOidcClient::setFailTokenExchange(true);
    session(['gate_auth_state' => 'valid_session_state']);

    $response = $this->get('/auth/gate/callback?code=bad_code&state=valid_session_state');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error');
    expect(Auth::check())->toBeFalse();
});

test('successful callback logs in user, regenerates session, and redirects to dashboard', function () {
    $userDTO = new GateUserInfoDTO(
        gateUserId: 'GATE-USR-SSO-001',
        name: 'Ustadz Salman Al-Farisi',
        email: 'salman@sabira.test',
        userType: 'tenaga_kesehatan',
        sourceStatus: 'active',
        appRoles: ['health_officer']
    );

    FakeGateOidcClient::addMockUser($userDTO);
    session(['gate_auth_state' => 'valid_session_state']);

    $response = $this->get('/auth/gate/callback?code=valid_code&state=valid_session_state');

    $response->assertRedirect(route('dashboard'));
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->name)->toBe('Ustadz Salman Al-Farisi');
    expect(Auth::user()->person->gate_user_id)->toBe('GATE-USR-SSO-001');
});

test('logout invalidates session and clears auth', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect();
    expect(Auth::check())->toBeFalse();
});
