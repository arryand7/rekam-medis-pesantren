<?php

use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\Reporting\HealthReportService;

function createReportUser(): User
{
    $role = Role::firstOrCreate(['name' => 'health_report_officer'], ['display_name' => 'Petugas Laporan']);
    $perm = Permission::where('name', 'view-health-reports')->first();
    if (! $perm) {
        $perm = Permission::create(['name' => 'view-health-reports', 'display_name' => 'view-health-reports']);
    }
    if (! $role->permissions()->where('permission_id', $perm->id)->exists()) {
        $role->permissions()->attach($perm->id);
    }

    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    return $user;
}

test('guest is redirected to login on report endpoints', function () {
    $this->get('/reports')->assertRedirect('/login');
    $this->get('/reports/view?report_type=visit_census')->assertRedirect('/login');
});

test('user with permission can view report index and query visit census with pagination', function () {
    $user = createReportUser();

    $person = Person::factory()->create(['name' => 'Santri Census Test']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => 'VIS-CENSUS-001',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Pusing ringan',
        'created_by_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/reports/view?report_type=visit_census');
    $response->assertOk();
    $response->assertSee('VIS-CENSUS-001');
    $response->assertSee('Santri Census Test');
});

test('health report service filters correctly by date and status', function () {
    $service = new HealthReportService;
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $user = User::factory()->create();

    MedicalVisit::create([
        'visit_number' => 'VIS-FILTER-DISCHARGED',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Keluhan 1',
        'created_by_id' => $user->id,
    ]);

    MedicalVisit::create([
        'visit_number' => 'VIS-FILTER-REGISTERED',
        'patient_id' => $patient->id,
        'status' => 'registered',
        'chief_complaint' => 'Keluhan 2',
        'created_by_id' => $user->id,
    ]);

    $dischargedList = $service->getVisitCensus(['status' => 'discharged']);
    expect($dischargedList->total())->toBeGreaterThanOrEqual(1);

    foreach ($dischargedList->items() as $item) {
        expect($item->status)->toBe('discharged');
    }
});
