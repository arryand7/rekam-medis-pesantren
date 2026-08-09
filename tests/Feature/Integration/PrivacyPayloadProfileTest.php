<?php

use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use App\Models\ActivityRestriction;
use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Models\VisitDischarge;
use App\Services\Integration\AttendanceDispositionPayloadBuilder;
use Illuminate\Support\Str;

test('dormitory supervisor payload strictly contains zero forbidden clinical keys', function () {
    $person = Person::factory()->create(['name' => 'Ahmad Santri', 'gate_user_id' => 'GATE-101']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => 'VISIT-TEST-DORM',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Demam dan pusing',
        'created_by_id' => User::factory()->create()->id,
    ]);

    $discharge = VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Putra',
        'clinical_summary' => 'SENSITIVE CLINICAL ASSESSMENT: Pasien mengalami ISPA akut.',
        'final_condition' => 'Membaik',
        'activity_recommendation' => 'light_activity',
        'rest_recommendation' => 'Istirahat di kamar asrama 1x24 jam',
        'restriction_notes' => 'Tidak boleh piket kebersihan berat',
        'follow_up_required' => true,
        'follow_up_date' => now()->addDays(2),
        'status' => 'finalized',
        'prepared_by_id' => User::factory()->create()->id,
    ]);

    $restriction = ActivityRestriction::create([
        'visit_discharge_id' => $discharge->id,
        'activity_status' => 'light_activity',
        'effective_start' => now(),
        'effective_until' => now()->addDays(2),
        'restriction_type' => 'dormitory_chores',
        'restriction_notes' => 'Bebas piket',
        'allowed_activity_notes' => 'Belajar santai',
        'status' => 'active',
        'issued_by_id' => User::factory()->create()->id,
        'issued_at' => now(),
    ]);

    $builder = new AttendanceDispositionPayloadBuilder;
    $dormPayload = $builder->buildDormSupervisorPayload($person, $discharge, $restriction);

    // Verify allowed operational keys
    expect($dormPayload['person_name'])->toBe('Ahmad Santri');
    expect($dormPayload['rest_recommendation'])->toBe('Istirahat di kamar asrama 1x24 jam');
    expect($dormPayload['practical_instructions'])->toBe('Tidak boleh piket kebersihan berat');

    // Assert ALL forbidden clinical keys are completely absent
    foreach (AttendanceDispositionPayloadBuilder::FORBIDDEN_CLINICAL_KEYS as $forbidden) {
        expect(array_key_exists($forbidden, $dormPayload))->toBeFalse("Forbidden key '{$forbidden}' must not be present in dorm payload");
    }

    // Ensure raw clinical narrative is not leaked in the payload string
    $json = json_encode($dormPayload);
    expect($json)->not->toContain('ISPA akut');
    expect($json)->not->toContain('Demam dan pusing');
});

test('homeroom teacher payload strictly contains zero forbidden clinical keys', function () {
    $person = Person::factory()->create(['name' => 'Fatimah Santriwati', 'gate_user_id' => 'GATE-102']);
    $patient = Patient::factory()->create(['person_id' => $person->id]);
    $visit = MedicalVisit::create([
        'visit_number' => 'VISIT-TEST-TEACHER',
        'patient_id' => $patient->id,
        'status' => 'discharged',
        'chief_complaint' => 'Nyeri perut berat',
        'created_by_id' => User::factory()->create()->id,
    ]);

    $discharge = VisitDischarge::create([
        'medical_visit_id' => $visit->id,
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Putri',
        'clinical_summary' => 'SENSITIVE CLINICAL ASSESSMENT: Dysmenorrhea primer berat.',
        'final_condition' => 'Stabil',
        'activity_recommendation' => 'avoid_sports',
        'rest_recommendation' => 'Boleh mengikuti KBM duduk',
        'restriction_notes' => 'Tidak mengikuti pelajaran olahraga fisik',
        'follow_up_required' => false,
        'status' => 'finalized',
        'prepared_by_id' => User::factory()->create()->id,
    ]);

    $restriction = ActivityRestriction::create([
        'visit_discharge_id' => $discharge->id,
        'activity_status' => 'avoid_sports',
        'effective_start' => now(),
        'effective_until' => now()->addDays(1),
        'restriction_type' => 'physical_education',
        'restriction_notes' => 'Bebas olahraga',
        'allowed_activity_notes' => 'KBM kelas teori',
        'status' => 'active',
        'issued_by_id' => User::factory()->create()->id,
        'issued_at' => now(),
    ]);

    $builder = new AttendanceDispositionPayloadBuilder;
    $teacherPayload = $builder->buildHomeroomTeacherPayload($person, $discharge, $restriction);

    // Verify allowed operational keys
    expect($teacherPayload['person_name'])->toBe('Fatimah Santriwati');
    expect($teacherPayload['school_activity_status'])->toBe('avoid_sports');
    expect($teacherPayload['attendance_accommodation'])->toBe('Tidak mengikuti pelajaran olahraga fisik');

    // Assert ALL forbidden clinical keys are absent
    foreach (AttendanceDispositionPayloadBuilder::FORBIDDEN_CLINICAL_KEYS as $forbidden) {
        expect(array_key_exists($forbidden, $teacherPayload))->toBeFalse("Forbidden key '{$forbidden}' must not be present in teacher payload");
    }

    $json = json_encode($teacherPayload);
    expect($json)->not->toContain('Dysmenorrhea');
    expect($json)->not->toContain('Nyeri perut berat');
});

test('attendance health disposition DTO throws exception if forbidden clinical key is injected', function () {
    expect(function () {
        new AttendanceHealthDispositionDTO(
            eventId: (string) Str::ulid(),
            eventVersion: 1,
            gateUserId: 'GATE-001',
            dispositionType: 'rest',
            effectiveFrom: now(),
            effectiveUntil: now()->addDay(),
            activityScope: 'all_activities',
            sourceVisitReference: 'VIS-001',
            issuedAt: now(),
            metadata: [
                'diagnosis' => 'Leaked Diagnosis', // FORBIDDEN KEY
            ]
        );
    })->toThrow(InvalidArgumentException::class, "Privacy violation: forbidden clinical key 'diagnosis' found in attendance disposition payload.");
});
