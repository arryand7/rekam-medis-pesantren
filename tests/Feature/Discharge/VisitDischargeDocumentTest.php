<?php

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Person;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\VisitDischargeDocumentService;
use App\Services\VisitDischargeService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('discharge_documents');
});

function createFinalizedDischargeForDocTest(): array
{
    $person = Person::factory()->create(['name' => 'Fulan Santri']);
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);

    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Gastritis akut',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Nyeri ulu hati setelah makan pedas',
        'examination_findings' => 'Nyeri tekan epigastrium',
        'assessment_summary' => 'Dispepsia / Gastritis',
        'working_diagnosis' => 'Gastritis',
        'disposition_recommendation' => 'home_care',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $dischargeService = new VisitDischargeService;
    $discharge = $dischargeService->prepareDraft($visit, [
        'discharge_type' => 'return_to_activity',
        'discharge_destination' => 'Asrama Santri',
        'clinical_summary' => 'Nyeri ulu hati teratasi dengan antasida.',
        'final_condition' => 'Nyeri hilang',
        'activity_recommendation' => 'full_activity',
    ], $officer);

    $finalized = $dischargeService->finalizeDischarge($discharge, [], $officer);
    $version = $finalized->versions()->where('version_number', 1)->firstOrFail();

    return [$finalized, $version, $officer];
}

test('generated discharge summary is stored on private discharge_documents disk with opaque filename', function () {
    [$discharge, $version, $officer] = createFinalizedDischargeForDocTest();
    $docService = new VisitDischargeDocumentService;

    $updatedVersion = $docService->generateDocument($version, $officer);

    expect($updatedVersion->document_status)->toBe('generated');
    expect($updatedVersion->document_path)->not->toBeNull();
    expect($updatedVersion->document_disk)->toBe('discharge_documents');

    // Verify opaque path: does NOT contain patient name or visit number
    expect($updatedVersion->document_path)->not->toContain('Fulan');
    expect($updatedVersion->document_path)->not->toContain($discharge->medicalVisit->visit_number);

    // Verify exists on fake private disk
    expect(Storage::disk('discharge_documents')->exists($updatedVersion->document_path))->toBeTrue();

    // Verify SHA-256 integrity
    $content = Storage::disk('discharge_documents')->get($updatedVersion->document_path);
    expect(hash('sha256', $content))->toBe($updatedVersion->document_checksum);
});

test('cannot generate document twice for the same version', function () {
    [$discharge, $version, $officer] = createFinalizedDischargeForDocTest();
    $docService = new VisitDischargeDocumentService;

    $docService->generateDocument($version, $officer);

    expect(fn () => $docService->generateDocument($version, $officer))
        ->toThrow(Exception::class, 'Dokumen untuk versi kepulangan ini sudah ada.');
});

test('downloading discharge document emits audit log and sets no-cache headers', function () {
    [$discharge, $version, $officer] = createFinalizedDischargeForDocTest();
    $docService = new VisitDischargeDocumentService;
    $updatedVersion = $docService->generateDocument($version, $officer);

    $response = $docService->streamDocument($updatedVersion, $officer);

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Cache-Control'))->toContain('no-store');

    $audit = AuditLog::where('action', 'discharge_summary.downloaded')
        ->where('subject_id', $version->id)
        ->count();
    expect($audit)->toBe(1);
});
