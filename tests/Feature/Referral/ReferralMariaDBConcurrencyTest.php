<?php

/**
 * MariaDB Concurrency Tests for the Referral Module.
 *
 * These tests MUST be run against a real MariaDB/MySQL instance.
 * SQLite does not support FOR UPDATE locking correctly and cannot
 * prove the concurrency invariants required by this module.
 *
 * To run:
 *   DB_CONNECTION=mysql DB_DATABASE=poskestren_health_test ./vendor/bin/pest tests/Feature/Referral/ReferralMariaDBConcurrencyTest.php --group=concurrency
 *
 * Or with testing env file:
 *   ./vendor/bin/pest --env=testing-mariadb --group=concurrency
 *
 * Required test database setup:
 *   CREATE DATABASE IF NOT EXISTS poskestren_health_test;
 *   GRANT ALL PRIVILEGES ON poskestren_health_test.* TO 'root'@'localhost';
 *   php artisan migrate --database=mysql_test (or switch DB_DATABASE)
 *
 * Evidence Requirements (see PHASE-3B-MARIADB-CONCURRENCY-REPORT.md):
 * - MariaDB version
 * - Isolation level (default: REPEATABLE READ)
 * - Number of concurrent goroutines/processes
 * - lockForUpdate() behavior
 * - Deadlock/retry outcome
 */

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\ReferralHandover;
use App\Models\ReferralReturn;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\DB;

function isMariaDbAvailable(): bool
{
    try {
        DB::statement('SELECT 1');

        return DB::connection()->getDriverName() !== 'sqlite';
    } catch (Throwable) {
        return false;
    }
}

/**
 * Test: Two concurrent requests creating an active referral for the same visit.
 * Expected: Only one succeeds, the other throws an exception.
 *
 * Note: True concurrency requires multi-process testing (e.g., parallel curl, amphp, or ReactPHP).
 * This test uses sequential simulation within a single process but verifies the
 * database-level constraint by running the guard logic explicitly.
 *
 * For true concurrent proof, use the shell script:
 *   bash tests/scripts/concurrency-referral-test.sh
 */
test('one-active-referral guard prevents duplicate within same transaction boundary', function () {
    if (! isMariaDbAvailable()) {
        test()->markTestSkipped('Requires MariaDB/MySQL — run: DB_CONNECTION=mysql ./vendor/bin/pest --group=concurrency');
    }

    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Concurrency test MariaDB',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Concurrency test case',
        'examination_findings' => 'Test finding',
        'assessment_summary' => 'Concurrency test',
        'working_diagnosis' => 'Test',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-CONCURRENCY-'.uniqid(),
        'name' => 'RS Concurrency Test',
        'partner_type' => 'hospital',
    ], $officer);

    // First create: must succeed
    $referral1 = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Test concurrency — pertama berhasil',
        'clinical_summary' => 'Test case concurrency MariaDB visit lock',
    ], $officer);

    expect($referral1->status)->toBe('prepared');

    // Second create on same visit: must fail with active guard
    expect(fn () => $referralService->createReferral($visit->fresh(), [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Test concurrency — kedua harus gagal',
        'clinical_summary' => 'Percobaan duplikasi rujukan aktif',
    ], $officer))->toThrow(Exception::class);

    // Verify only ONE referral exists for this visit
    $count = Referral::where('medical_visit_id', $visit->id)->count();
    expect($count)->toBe(1);

    // Verify only ONE audit log exists for successful referral creation
    $auditCount = AuditLog::where('action', 'referral.created')
        ->where('subject_id', $referral1->id)
        ->count();
    expect($auditCount)->toBe(1);
})->group('concurrency');

test('referral numbers are unique under sequential high-volume generation (MariaDB)', function () {
    if (! isMariaDbAvailable()) {
        test()->markTestSkipped('Requires MariaDB/MySQL — run: DB_CONNECTION=mysql ./vendor/bin/pest --group=concurrency');
    }

    // Generate 50 referral numbers rapidly in sequence and verify uniqueness
    $numbers = array_map(
        fn () => Referral::generateReferralNumber(),
        range(1, 50)
    );

    $unique = array_unique($numbers);
    expect(count($unique))->toBe(50, 'Referral numbers must be unique under rapid sequential generation');
})->group('concurrency');

test('handoff idempotency key prevents duplicate handover records (MariaDB)', function () {
    if (! isMariaDbAvailable()) {
        test()->markTestSkipped('Requires MariaDB/MySQL — run: DB_CONNECTION=mysql ./vendor/bin/pest --group=concurrency');
    }

    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Handoff idempotency MariaDB test',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Test',
        'examination_findings' => 'Test',
        'assessment_summary' => 'Test summary',
        'working_diagnosis' => 'Test dx',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-IDEMPOTENCY-'.uniqid(),
        'name' => 'RS Idempotency Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'urgent',
        'reason' => 'Idempotency test alasan cukup panjang',
        'clinical_summary' => 'Idempotency test summary untuk MariaDB',
    ], $officer);

    $referralService->recordDeparture($referral, [], $officer);

    $idempotencyKey = 'handoff-mariadb-test-'.uniqid();

    // Submit handoff twice with same idempotency key
    $h1 = $referralService->recordHandover($referral->fresh(), ['idempotency_key' => $idempotencyKey, 'notes' => 'First submission'], $officer);
    $h2 = $referralService->recordHandover($referral->fresh(), ['idempotency_key' => $idempotencyKey, 'notes' => 'Duplicate submission'], $officer);

    // Both must return the same record
    expect($h1->id)->toBe($h2->id);

    // Only ONE handover record must exist with this key
    $count = ReferralHandover::where('idempotency_key', $idempotencyKey)->count();
    expect($count)->toBe(1);
})->group('concurrency');

test('one-return guard prevents duplicate return records (MariaDB)', function () {
    if (! isMariaDbAvailable()) {
        test()->markTestSkipped('Requires MariaDB/MySQL — run: DB_CONNECTION=mysql ./vendor/bin/pest --group=concurrency');
    }

    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'One-return guard MariaDB test',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Test',
        'examination_findings' => 'Test',
        'assessment_summary' => 'Test',
        'working_diagnosis' => 'Test',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-RETURN-GUARD-'.uniqid(),
        'name' => 'RS Return Guard Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'Return guard test alasan harus cukup panjang untuk valid',
        'clinical_summary' => 'Return guard test summary untuk MariaDB uniqueness',
    ], $officer);

    $referralService->recordDeparture($referral, [], $officer);

    // First return — succeeds
    $referralService->recordReturn($referral->fresh(), [
        'external_outcome_summary' => 'Pasien menjalani evaluasi dan kembali dalam kondisi baik',
    ], $officer);

    // Second return — must fail (status now 'returned')
    expect(fn () => $referralService->recordReturn($referral->fresh(), [
        'external_outcome_summary' => 'Kepulangan duplikat tidak boleh berhasil',
    ], $officer))->toThrow(Exception::class);

    // Only one return record must exist
    $count = ReferralReturn::where('referral_id', $referral->id)->count();
    expect($count)->toBe(1);
})->group('concurrency');
