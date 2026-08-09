<?php

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use Illuminate\Support\Facades\Route;

function createTestDoctorUser(): User
{
    $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'display_name' => 'Dokter']);
    $permissions = [
        'view-visit-discharges',
        'prepare-visit-discharges',
        'finalize-visit-discharges',
        'amend-visit-discharges',
        'manage-follow-up-plans',
        'manage-activity-restrictions',
        'prepare-operational-handoffs',
        'acknowledge-operational-handoffs',
        'download-discharge-summaries',
    ];

    foreach ($permissions as $permName) {
        $perm = Permission::where('name', $permName)->first();
        if (! $perm) {
            $perm = Permission::create(['name' => $permName, 'display_name' => $permName]);
        }
        if (! $doctorRole->permissions()->where('permission_id', $perm->id)->exists()) {
            $doctorRole->permissions()->attach($perm->id);
        }
    }

    $doctor = User::factory()->create();
    $doctor->roles()->attach($doctorRole->id);

    return $doctor;
}

function createTestVisitForAuth(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Evaluasi rujukan kembali',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Kontrol pasca rujukan',
        'examination_findings' => 'Keadaan umum baik',
        'assessment_summary' => 'Kondisi stabil',
        'working_diagnosis' => 'Post rujukan',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    return [$visit, $officer];
}

test('guest is redirected to login when accessing discharge endpoints', function () {
    [$visit] = createTestVisitForAuth();

    $this->get('/discharges')->assertRedirect('/login');
    $this->get("/visits/{$visit->id}/discharge")->assertRedirect('/login');
    $this->post("/visits/{$visit->id}/discharge", [])->assertRedirect('/login');
});

test('user without permission receives 403 forbidden on discharge store', function () {
    [$visit] = createTestVisitForAuth();
    $unprivilegedUser = User::factory()->create(); // No roles or permissions

    $this->actingAs($unprivilegedUser)
        ->post("/visits/{$visit->id}/discharge", [
            'discharge_type' => 'return_to_activity',
            'discharge_destination' => 'Asrama',
            'clinical_summary' => 'Pasien telah sembuh total.',
            'final_condition' => 'Sembuh',
            'activity_recommendation' => 'full_activity',
        ])
        ->assertForbidden();
});

test('doctor with permissions can prepare draft and finalize discharge', function () {
    [$visit] = createTestVisitForAuth();
    $doctor = createTestDoctorUser();

    $response = $this->actingAs($doctor)
        ->post("/visits/{$visit->id}/discharge", [
            'discharge_type' => 'return_to_activity',
            'discharge_destination' => 'Asrama Putra',
            'clinical_summary' => 'Pasien telah sembuh total dan siap beraktivitas.',
            'final_condition' => 'Sembuh',
            'activity_recommendation' => 'full_activity',
        ]);

    $response->assertRedirect(route('visits.discharge', $visit->id));

    $discharge = VisitDischarge::where('medical_visit_id', $visit->id)->firstOrFail();

    $finalizeResponse = $this->actingAs($doctor)
        ->post("/discharges/{$discharge->id}/finalize", []);

    $finalizeResponse->assertRedirect(route('discharges.show', $discharge->id));
    expect($discharge->fresh()->status)->toBe('finalized');
});

test('all discharge routes are handled by dedicated controllers without closures', function () {
    $dischargeRoutes = [
        'discharges.index',
        'visits.discharge',
        'visits.discharge.store',
        'discharges.show',
        'discharges.finalize',
        'discharges.amend',
        'follow-up-plans.index',
        'discharges.follow-up-plans.store',
        'follow-up-plans.complete',
        'follow-up-plans.cancel',
        'discharges.activity-restrictions.store',
        'activity-restrictions.cancel',
        'operational-handoffs.index',
        'discharges.operational-handoffs.store',
        'operational-handoffs.acknowledge',
        'discharges.document.download',
        'discharges.document.generate',
    ];

    foreach ($dischargeRoutes as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);
        expect($route)->not->toBeNull("Route {$routeName} must exist");
        expect($route->getActionName())->not->toBe('Closure', "Route {$routeName} must not use closure");
        expect($route->getActionName())->toContain('App\Http\Controllers\Discharge\\');
    }
});
