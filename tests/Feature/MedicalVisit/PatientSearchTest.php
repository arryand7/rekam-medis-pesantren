<?php

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;

function patientSearchUser(bool $authorized = true): User
{
    $role = Role::create([
        'name' => 'patient_search_'.uniqid(),
        'display_name' => 'Patient Search Test',
    ]);

    if ($authorized) {
        $permission = Permission::firstOrCreate(
            ['name' => 'create-medical-visits'],
            ['display_name' => 'Registrasi Kunjungan Medis (Intake)']
        );
        $role->permissions()->attach($permission);
    }

    $user = User::factory()->create();
    $user->roles()->attach($role);

    return $user;
}

test('visit intake uses an accessible server side patient combobox instead of rendering every patient', function () {
    $officer = patientSearchUser();
    $visiblePerson = Person::factory()->create(['name' => 'Pasien Prefill', 'nis_nip' => 'NIS-PREFILL']);
    $visiblePatient = Patient::factory()->create([
        'person_id' => $visiblePerson->id,
        'patient_number' => 'RM-PREFILL',
    ]);
    $unrelatedPerson = Person::factory()->create(['name' => 'Pasien Tidak Dimuat Massal']);
    Patient::factory()->create(['person_id' => $unrelatedPerson->id]);

    $response = $this->actingAs($officer)->get(route('visits.create', ['patient_id' => $visiblePatient->id]));

    $response->assertOk()
        ->assertSee('Cari dan Pilih Pasien')
        ->assertSee('Ketik nama, nomor RM, atau NIS/NIP')
        ->assertSee('role="combobox"', false)
        ->assertSee('role="listbox"', false)
        ->assertSee('Pasien Prefill')
        ->assertDontSee('Pasien Tidak Dimuat Massal');
});

test('authorized intake officer can search eligible patients by name medical record number and nis nip', function (string $term) {
    $officer = patientSearchUser();
    $person = Person::factory()->create([
        'name' => 'Nur Aisyah Rahma',
        'nis_nip' => 'NIS-SEARCH-7788',
        'user_type' => 'santri',
    ]);
    Patient::factory()->create([
        'person_id' => $person->id,
        'patient_number' => 'RM-SEARCH-9911',
        'is_eligible' => true,
    ]);

    $response = $this->actingAs($officer)->getJson(route('visits.patient-search', ['q' => $term]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Nur Aisyah Rahma')
        ->assertJsonPath('data.0.patient_number', 'RM-SEARCH-9911')
        ->assertJsonPath('data.0.nis_nip', 'NIS-SEARCH-7788')
        ->assertJsonMissingPath('data.0.nik')
        ->assertJsonMissingPath('data.0.email')
        ->assertJsonMissingPath('data.0.phone');
})->with([
    'name' => 'Aisyah',
    'medical record number' => '9911',
    'NIS/NIP' => '7788',
]);

test('patient search excludes ineligible records and limits responses to twenty results', function () {
    $officer = patientSearchUser();

    foreach (range(1, 25) as $index) {
        $person = Person::factory()->create(['name' => sprintf('Pencarian Massal %02d', $index)]);
        Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    }

    $ineligiblePerson = Person::factory()->create(['name' => 'Pencarian Massal Tidak Layak']);
    Patient::factory()->create(['person_id' => $ineligiblePerson->id, 'is_eligible' => false]);

    $response = $this->actingAs($officer)->getJson(route('visits.patient-search', ['q' => 'Pencarian Massal']));

    $response->assertOk()->assertJsonCount(20, 'data');
    expect(collect($response->json('data'))->pluck('name'))->not->toContain('Pencarian Massal Tidak Layak');
});

test('patient search validates query length and enforces intake authorization', function () {
    $authorized = patientSearchUser();
    $unauthorized = patientSearchUser(false);

    $invalidResponse = $this->actingAs($authorized)
        ->getJson(route('visits.patient-search', ['q' => 'A']));
    $invalidResponse->assertUnprocessable()->assertJsonValidationErrors('q');

    $this->actingAs($unauthorized)
        ->getJson(route('visits.patient-search', ['q' => 'Aisyah']))
        ->assertForbidden();
});
