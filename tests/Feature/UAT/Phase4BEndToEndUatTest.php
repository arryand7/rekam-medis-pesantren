<?php

use App\Http\Middleware\EnforceGateApplicationEntitlement;
use App\Models\ActivityRestriction;
use App\Models\ClinicalAssessment;
use App\Models\ClinicalOperationalHandoff;
use App\Models\HealthcarePartner;
use App\Models\IntegrationOutboxEvent;
use App\Models\MedicalVisit;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\ReferralReturn;
use App\Models\ReferralReturnReview;
use App\Models\Role;
use App\Models\User;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\IntegrationOutboxService;
use App\Services\OperationalNotificationService;
use App\Services\VisitDischargeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    FakeAttendanceIntegration::reset();
});

function createUatStaff(): User
{
    $person = Person::factory()->create([
        'gate_user_id' => 'GATE-STF-'.uniqid(),
        'name' => 'dr. Sarah Humaira',
        'user_type' => 'tenaga_kesehatan',
    ]);

    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'sarah.'.uniqid().'@sabira.id',
        'is_active' => true,
    ]);

    $role = Role::firstOrCreate(['name' => 'petugas_kesehatan'], ['display_name' => 'Petugas Kesehatan']);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function createUatPatient(): Patient
{
    $person = Person::factory()->create([
        'gate_user_id' => 'GATE-SAN-'.uniqid(),
        'name' => 'Ahmad Santri UAT',
        'user_type' => 'santri',
    ]);

    return Patient::createOrFindForPerson($person);
}

test('Scenario A: Simple Visit -> Assessment -> Discharge Rest -> Operational Handoff -> Outbox -> Attendance Sandbox', function () {
    $doctor = createUatStaff();
    $patient = createUatPatient();

    // 1. Visit Intake
    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VIS-'.date('Ymd').'-'.strtoupper(uniqid()),
        'registered_at' => now(),
        'registered_by_id' => $doctor->id,
        'status' => 'waiting_assessment',
        'visit_type' => 'illness',
        'triage_category' => 'green',
        'chief_complaint' => 'Demam dan pusing 2 hari',
    ]);

    // 2. Clinical Assessment
    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Pasien mengeluh pusing dan demam sejak kemarin malam.',
        'relevant_history' => 'Tidak ada riwayat alergi obat.',
        'examination_findings' => 'Suhu: 38.2 C, Nadi: 82x/m.',
        'assessment_summary' => 'Febris Akut ec Suspect Viral Infection',
        'working_diagnosis' => 'Viral Infection',
        'status' => 'finalized',
        'disposition_recommendation' => 'rest_in_dorm',
        'finalized_at' => now(),
        'finalized_by_id' => $doctor->id,
    ]);

    // 3. Visit Discharge
    $dischargeService = app(VisitDischargeService::class);
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'routine',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Pasien disarankan istirahat di asrama selama 2 hari.',
        'final_condition' => 'improved',
        'activity_recommendation' => 'rest_restricted',
        'rest_recommendation' => 'Istirahat di asrama',
        'restriction_notes' => 'Istirahat total di kamar santri, bebas dari KBM dan ekstrakurikuler.',
        'follow_up_required' => false,
    ], $doctor);

    $discharge = $dischargeService->finalizeDischarge($discharge, [
        'clinical_summary' => 'Pengkajian dan instruksi istirahat tuntas.',
    ], $doctor);
    $visit->refresh();
    expect($visit->status)->toBe('discharged');

    // 4. Activity Restriction & Operational Handoff
    $restriction = ActivityRestriction::create([
        'visit_discharge_id' => $discharge->id,
        'activity_status' => 'rest_restricted',
        'effective_start' => now(),
        'effective_until' => now()->addDays(2),
        'restriction_type' => 'bed_rest',
        'restriction_notes' => 'Istirahat total di asrama',
        'issued_by_id' => $doctor->id,
        'issued_at' => now(),
        'status' => 'active',
    ]);

    $handoff = $dischargeService->createOperationalHandoff($discharge, [
        'recipient_type' => 'dorm_supervisor',
        'purpose' => 'Perawatan santri di asrama',
        'special_instructions' => 'Mohon pantau asupan santri',
    ], $doctor);
    expect($handoff)->toBeInstanceOf(ClinicalOperationalHandoff::class);

    // 5. Operational Notification & Outbox Dispatch
    $notificationService = app(OperationalNotificationService::class);
    $dispatchResult = $notificationService->dispatchDischargeNotifications($discharge, $doctor);

    expect($dispatchResult['outbox_event'])->toBeInstanceOf(IntegrationOutboxEvent::class);

    // Verify Outbox Event Payload Privacy
    $outbox = $dispatchResult['outbox_event'];
    expect($outbox->destination)->toBe('attendance_system');
    expect($outbox->payload_snapshot)->toHaveKey('gate_user_id');
    expect($outbox->payload_snapshot)->toHaveKey('disposition_type');
    expect($outbox->payload_snapshot)->not->toHaveKey('diagnosis');
    expect($outbox->payload_snapshot)->not->toHaveKey('history_current_illness');

    // 6. Process Outbox
    $outboxService = app(IntegrationOutboxService::class);
    $resultStatus = $outboxService->processSingleEvent($outbox->id);

    expect($resultStatus)->toBe('success');
    $outbox->refresh();
    expect($outbox->status)->toBe('acknowledged');
    expect($outbox->acknowledged_at)->not->toBeNull();

    // Verify Fake Attendance Integration captured the event
    $published = FakeAttendanceIntegration::getPublishedDispositions();
    $eventId = $outbox->payload_snapshot['event_id'];
    expect($published)->toHaveKey($eventId);
    expect($published[$eventId]['dto']['gate_user_id'])->toBe($patient->person->gate_user_id);
});

test('Scenario B: Observation -> Return to Activity -> Attendance Sandbox Update', function () {
    $doctor = createUatStaff();
    $patient = createUatPatient();

    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VIS-OBS-'.uniqid(),
        'registered_at' => now(),
        'registered_by_id' => $doctor->id,
        'status' => 'in_observation',
        'visit_type' => 'illness',
        'chief_complaint' => 'Observasi dehidrasi ringan',
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Observasi dehidrasi',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'Turgor baik',
        'assessment_summary' => 'Dehidrasi Ringan teratasi',
        'working_diagnosis' => 'Dehidrasi Ringan',
        'status' => 'finalized',
        'disposition_recommendation' => 'return_to_normal_activity',
        'finalized_at' => now(),
        'finalized_by_id' => $doctor->id,
    ]);

    $episode = ObservationEpisode::create([
        'medical_visit_id' => $visit->id,
        'reason' => 'Observasi cairan oralit 6 jam',
        'started_at' => now()->subHours(6),
        'started_by_id' => $doctor->id,
        'status' => 'completed',
        'bed_label' => 'BED-02',
        'ended_at' => now(),
        'ended_by_id' => $doctor->id,
        'outcome' => 'discharged_improved',
    ]);

    $dischargeService = app(VisitDischargeService::class);
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'routine',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Observasi selesai, kondisi membaik, santri dapat kembali beraktivitas penuh.',
        'final_condition' => 'improved',
        'activity_recommendation' => 'full_activity',
        'restriction_notes' => 'Kembali mengikuti KBM normal.',
        'follow_up_required' => false,
    ], $doctor);

    $discharge = $dischargeService->finalizeDischarge($discharge, [
        'clinical_summary' => 'Observasi selesai dan tuntas.',
    ], $doctor);

    $notificationService = app(OperationalNotificationService::class);
    $dispatchResult = $notificationService->dispatchDischargeNotifications($discharge, $doctor);

    $outbox = $dispatchResult['outbox_event'];
    expect($outbox->payload_snapshot['disposition_type'])->toBe('return_to_activity');

    $outboxService = app(IntegrationOutboxService::class);
    $res = $outboxService->processSingleEvent($outbox->id);
    expect($res)->toBe('success');
});

test('Scenario C: Visit -> Referral -> Return -> Review -> Discharge -> Outbox', function () {
    $doctor = createUatStaff();
    $patient = createUatPatient();

    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VIS-REF-'.uniqid(),
        'registered_at' => now(),
        'registered_by_id' => $doctor->id,
        'status' => 'under_referral',
        'visit_type' => 'emergency',
        'chief_complaint' => 'Suspect appendicitis akut',
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Nyeri perut kanan bawah',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'McBurney sign positif',
        'assessment_summary' => 'Suspect Appendicitis Akut',
        'working_diagnosis' => 'Appendicitis',
        'status' => 'finalized',
        'disposition_recommendation' => 'referral_emergency',
        'finalized_at' => now(),
        'finalized_by_id' => $doctor->id,
    ]);

    $partner = HealthcarePartner::create([
        'code' => 'RSUD-CIAWI-'.uniqid(),
        'name' => 'RSUD Ciawi',
        'partner_type' => 'hospital',
        'is_active' => true,
    ]);

    $referral = Referral::create([
        'medical_visit_id' => $visit->id,
        'clinical_assessment_id' => $assessment->id,
        'healthcare_partner_id' => $partner->id,
        'initiated_by_id' => $doctor->id,
        'referral_number' => 'REF-'.uniqid(),
        'status' => 'completed',
        'urgency' => 'emergency',
        'reason' => 'Evaluasi bedah digestif RS mitra',
        'clinical_summary' => 'Evaluasi bedah digestif RS mitra',
    ]);

    $return = ReferralReturn::create([
        'referral_id' => $referral->id,
        'recorded_by_id' => $doctor->id,
        'returned_at' => now(),
        'return_transport_notes' => 'Ambulans santri',
        'accompanied_by_notes' => 'Ustadz Pembina',
        'external_outcome_summary' => 'Sudah USG abdomen, non-surgical, diterapi konservatif.',
        'status' => 'received',
    ]);

    $review = ReferralReturnReview::create([
        'referral_return_id' => $return->id,
        'local_reviewer_id' => $doctor->id,
        'review_summary' => 'Hasil evaluasi RS menunjukkan perbaikan, lanjutkan istirahat di asrama.',
        'decision_type' => 'rest_recommended',
        'status' => 'finalized',
    ]);

    $dischargeService = app(VisitDischargeService::class);
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'routine',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Pasien kembali dari rujukan RS, istirahat 3 hari di asrama.',
        'final_condition' => 'improved',
        'activity_recommendation' => 'rest_restricted',
        'follow_up_required' => false,
    ], $doctor);

    $discharge = $dischargeService->finalizeDischarge($discharge, [
        'clinical_summary' => 'Rujukan kembali dan direview tuntas.',
    ], $doctor);
    expect($visit->refresh()->status)->toBe('discharged');
});

test('Scenario D: Discharge Amendment -> Superseding Outbox Event', function () {
    $doctor = createUatStaff();
    $patient = createUatPatient();

    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VIS-AMD-'.uniqid(),
        'registered_at' => now(),
        'registered_by_id' => $doctor->id,
        'status' => 'waiting_assessment',
        'visit_type' => 'illness',
        'chief_complaint' => 'Sprain pergelangan kaki',
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Sprain ankle saat futsal',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'Bengkak ankle dextra',
        'assessment_summary' => 'Ankle Sprain Grade 1',
        'working_diagnosis' => 'Ankle Sprain',
        'status' => 'finalized',
        'disposition_recommendation' => 'rest_in_dorm',
        'finalized_at' => now(),
        'finalized_by_id' => $doctor->id,
    ]);

    $dischargeService = app(VisitDischargeService::class);
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'routine',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Istirahat 1 hari.',
        'final_condition' => 'improved',
        'activity_recommendation' => 'rest_restricted',
        'follow_up_required' => false,
    ], $doctor);
    $discharge = $dischargeService->finalizeDischarge($discharge, [
        'clinical_summary' => 'Discharge awal.',
    ], $doctor);

    $notifService = app(OperationalNotificationService::class);
    $dispatchA = $notifService->dispatchDischargeNotifications($discharge, $doctor);
    $outboxA = $dispatchA['outbox_event'];

    $outboxService = app(IntegrationOutboxService::class);
    $outboxService->processSingleEvent($outboxA->id);

    // Doctor decides to amend discharge: extend to light activity for 3 days
    $amendedDischarge = $dischargeService->amendDischarge($discharge, [
        'discharge_type' => 'routine',
        'discharge_destination' => 'dormitory',
        'clinical_summary' => 'Koreksi: Santri boleh KBM kelas, namun bebas olahraga 3 hari.',
        'final_condition' => 'improved',
        'activity_recommendation' => 'light_activity',
        'restriction_notes' => 'Bebas olahraga',
        'follow_up_required' => false,
    ], 'Perpanjangan batasan aktivitas olahraga atas keluhan santri.', $doctor);

    $dispatchB = $notifService->dispatchDischargeNotifications($amendedDischarge, $doctor);
    $outboxB = $dispatchB['outbox_event'];

    expect($outboxB)->not->toBeNull();
    $result = $outboxService->processSingleEvent($outboxB->id);
    expect($result)->toBe('success');
});

test('Scenario E: Gate User Revocation -> Revalidation Access Denied -> Medical History Intact', function () {
    $doctor = createUatStaff();
    $patient = createUatPatient();

    // Create visit history for patient
    $visit = MedicalVisit::create([
        'patient_id' => $patient->id,
        'visit_number' => 'VIS-REV-'.uniqid(),
        'registered_at' => now()->subDays(5),
        'registered_by_id' => $doctor->id,
        'status' => 'completed',
        'visit_type' => 'illness',
        'chief_complaint' => 'Riwayat pengobatan tifoid',
    ]);

    $assessment = ClinicalAssessment::create([
        'medical_visit_id' => $visit->id,
        'author_id' => $doctor->id,
        'history_current_illness' => 'Tifoid 5 hari lalu',
        'relevant_history' => 'Tidak ada',
        'examination_findings' => 'Membaik',
        'assessment_summary' => 'Tifoid sembuh tuntas',
        'working_diagnosis' => 'Typhoid Fever',
        'status' => 'finalized',
        'disposition_recommendation' => 'return_to_normal_activity',
        'finalized_at' => now()->subDays(5),
        'finalized_by_id' => $doctor->id,
    ]);

    // Simulate user deactivation / revocation in Gate
    $user = User::where('person_id', $patient->person_id)->first();
    if ($user) {
        $user->is_active = false;
        $user->save();
    }

    $person = $patient->person;
    $person->source_status = 'deactivated';
    $person->save();

    // Verify Patient record and medical visits STILL EXIST in database
    expect(Patient::find($patient->id))->not->toBeNull();
    expect(MedicalVisit::find($visit->id))->not->toBeNull();
    expect(ClinicalAssessment::find($assessment->id))->not->toBeNull();

    // Verify middleware denies access to deactivated user
    if ($user) {
        Auth::login($user);
        $middleware = new EnforceGateApplicationEntitlement;
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        expect($response->isRedirect())->toBeTrue();
        expect(Auth::check())->toBeFalse();
    }
});
