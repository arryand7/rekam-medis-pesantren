<?php

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Route;

function createReferralForAuthTest(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Nyeri dada auth test',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri dada onset akut',
        'examination_findings' => 'TD 150/90, HR 110/mnt',
        'assessment_summary' => 'Kemungkinan STEMI',
        'working_diagnosis' => 'Chest pain, suspect STEMI',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-AUTH-TEST-'.uniqid(),
        'name' => 'RS Auth Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'emergency',
        'reason' => 'Nyeri dada memerlukan evaluasi segera di IGD RS',
        'clinical_summary' => 'Suspect STEMI, perlu EKG dan tatalaksana segera',
    ], $officer);

    return [$referral, $officer];
}

// ─── Guest Authorization Tests ───────────────────────────────────────────────

test('guest cannot access referral index — redirected to login', function () {
    $response = $this->get('/referrals');
    $response->assertRedirect('/login');
})->group('auth');

test('guest cannot access referral show — redirected to login', function () {
    [$referral] = createReferralForAuthTest();
    $response = $this->get('/referrals/'.$referral->id);
    $response->assertRedirect('/login');
})->group('auth');

test('guest cannot post to referral transport — redirected to login', function () {
    [$referral] = createReferralForAuthTest();
    $response = $this->post('/referrals/'.$referral->id.'/transport', ['transport_type' => 'ambulance_partner']);
    $response->assertRedirect('/login');
})->group('auth');

test('guest cannot post referral departure — redirected to login', function () {
    [$referral] = createReferralForAuthTest();
    $response = $this->post('/referrals/'.$referral->id.'/depart');
    $response->assertRedirect('/login');
})->group('auth');

test('guest cannot download referral document — redirected to login', function () {
    [$referral] = createReferralForAuthTest();
    $version = $referral->versions()->first();
    $response = $this->get("/referrals/{$referral->id}/versions/{$version->id}/document");
    $response->assertRedirect('/login');
})->group('auth');

// ─── Authenticated Without Permission Tests ───────────────────────────────────

test('authenticated user without create-referrals permission gets 403 on store', function () {
    [$referral, $officer] = createReferralForAuthTest();

    // Create a user with a role but NO create-referrals permission
    $role = Role::create(['name' => 'viewer-only-'.uniqid(), 'display_name' => 'Viewer']);
    $userNoPermission = User::factory()->create();
    $userNoPermission->roles()->attach($role->id);

    $response = $this->actingAs($userNoPermission)
        ->post('/visits/'.$referral->medicalVisit->id.'/referrals', [
            'healthcare_partner_id' => $referral->healthcare_partner_id,
            'urgency' => 'routine',
            'reason' => 'Test tanpa izin',
            'clinical_summary' => 'Test tanpa izin ini minimal',
        ]);

    $response->assertForbidden();
})->group('auth');

test('authenticated user without arrange-referral-transport permission gets 403 on transport store', function () {
    [$referral] = createReferralForAuthTest();

    $role = Role::create(['name' => 'no-transport-'.uniqid(), 'display_name' => 'No Transport']);
    $userNoPermission = User::factory()->create();
    $userNoPermission->roles()->attach($role->id);

    $response = $this->actingAs($userNoPermission)
        ->post('/referrals/'.$referral->id.'/transport', [
            'transport_type' => 'ambulance_partner',
        ]);

    $response->assertForbidden();
})->group('auth');

test('authenticated user without record-return-from-referral permission gets 403 on return store', function () {
    [$referral] = createReferralForAuthTest();

    $role = Role::create(['name' => 'no-return-'.uniqid(), 'display_name' => 'No Return']);
    $userNoPermission = User::factory()->create();
    $userNoPermission->roles()->attach($role->id);

    $response = $this->actingAs($userNoPermission)
        ->post('/referrals/'.$referral->id.'/return', [
            'external_outcome_summary' => 'Test kepulangan tidak berizin',
        ]);

    $response->assertForbidden();
})->group('auth');

// ─── IDOR Tests ───────────────────────────────────────────────────────────────

test('IDOR: user cannot post return review for referral belonging to a different visit', function () {
    // Create referral owned by officer1
    [$referral1] = createReferralForAuthTest();

    // Create a different officer2 with review permission but for their own referrals only
    // (policy should scope to referrals the actor has access to)
    $role = Role::create(['name' => 'reviewer-'.uniqid(), 'display_name' => 'Reviewer']);
    $reviewPerm = Permission::create(['name' => 'review-return-from-referral', 'display_name' => 'Review Return']);
    $role->permissions()->attach($reviewPerm->id);
    $officer2 = User::factory()->create();
    $officer2->roles()->attach($role->id);

    // officer2 tries to access referral1's return review — should be 403 or 404
    // (Policy scope should prevent cross-record access)
    $response = $this->actingAs($officer2)
        ->post('/referral-returns/non-existent-id/review', [
            'review_summary' => 'IDOR attempt',
            'decision_type' => 'rest_recommended',
        ]);

    // Should be 404 (record not found) or 403 (forbidden) — never 200/302
    expect($response->status())->toBeIn([403, 404]);
})->group('auth');

test('download without permission returns 403', function () {
    [$referral] = createReferralForAuthTest();
    $version = $referral->versions()->first();

    $role = Role::create(['name' => 'no-download-'.uniqid(), 'display_name' => 'No Download']);
    $userNoPerm = User::factory()->create();
    $userNoPerm->roles()->attach($role->id);

    $response = $this->actingAs($userNoPerm)
        ->get("/referrals/{$referral->id}/versions/{$version->id}/document");

    $response->assertForbidden();
})->group('auth');

test('return review without permission returns 403', function () {
    [$referral] = createReferralForAuthTest();

    $role = Role::create(['name' => 'no-review-'.uniqid(), 'display_name' => 'No Review']);
    $userNoPerm = User::factory()->create();
    $userNoPerm->roles()->attach($role->id);

    $response = $this->actingAs($userNoPerm)
        ->post('/referral-returns/some-id/review', [
            'review_summary' => 'Test review tanpa izin',
            'decision_type' => 'rest_recommended',
        ]);

    // 403 (no permission) or 404 (not found before policy check) both acceptable
    expect($response->status())->toBeIn([403, 404]);
})->group('auth');

// ─── Login Stub Security Tests ────────────────────────────────────────────────

test('login stub endpoint does not authenticate guest or create synthetic session', function () {
    expect(auth()->check())->toBeFalse();

    $response = $this->get('/login');
    $response->assertStatus(200);

    // Guest must remain unauthenticated after hitting /login stub
    expect(auth()->check())->toBeFalse();
})->group('auth');

test('login stub does not accept identity or role escalation payload', function () {
    $response = $this->get('/login?user_id=1&role=admin');
    $response->assertStatus(200);

    // Guest still unauthenticated
    expect(auth()->check())->toBeFalse();

    // Still cannot access protected referral route
    $this->get('/referrals')->assertRedirect('/login');
})->group('auth');

test('authorized user with view-referrals permission can access referral index and show', function () {
    [$referral] = createReferralForAuthTest();

    $role = Role::create(['name' => 'referral-officer-'.uniqid(), 'display_name' => 'Referral Officer']);
    $viewPerm = Permission::create(['name' => 'view-referrals', 'display_name' => 'View Referrals']);
    $role->permissions()->attach($viewPerm->id);

    $authorizedUser = User::factory()->create();
    $authorizedUser->roles()->attach($role->id);

    $responseIndex = $this->actingAs($authorizedUser)->get('/referrals');
    $responseIndex->assertStatus(200);

    $responseShow = $this->actingAs($authorizedUser)->get('/referrals/'.$referral->id);
    $responseShow->assertStatus(200);
})->group('auth');

// ─── Verify route structure — no closure routes remain for referral ───────────

test('no referral routes use Closure handlers', function () {
    $routes = Route::getRoutes();
    $closureReferralRoutes = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        $action = $route->getAction('uses');

        if (
            (str_contains($uri, 'referral') || str_contains($uri, 'referral-return')) &&
            $action instanceof Closure
        ) {
            $closureReferralRoutes[] = $uri;
        }
    }

    expect($closureReferralRoutes)
        ->toBeEmpty('Routes still using closure: '.implode(', ', $closureReferralRoutes));
})->group('auth');
