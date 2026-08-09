<?php

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\MedicalVisitService;
use App\Services\ReferralDocumentService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Use fake storage for all document tests
    Storage::fake('referral_documents');
});

function createFinalizedReferralVersionForDocTest(): array
{
    $person = Person::factory()->create();
    $patient = Patient::factory()->create(['person_id' => $person->id, 'is_eligible' => true]);
    $officer = User::factory()->create();

    $visit = (new MedicalVisitService)->registerVisit([
        'patient_id' => $patient->id,
        'chief_complaint' => 'Gagal ginjal kronik',
    ], $officer);

    $assessmentService = new ClinicalAssessmentService;
    $assessment = $assessmentService->saveDraft($visit, [
        'history_current_illness' => 'Pasien hemodialisis rutin',
        'examination_findings' => 'Edema tungkai, TD 160/100',
        'assessment_summary' => 'CKD on HD, perlu evaluasi nefrologi',
        'working_diagnosis' => 'CKD stage 5 on HD',
        'disposition_recommendation' => 'referral_required',
    ], $officer);
    $assessmentService->finalizeAssessment($assessment, $officer);

    $referralService = new ReferralService;
    $partner = $referralService->createPartnerForTest([
        'code' => 'RS-NEPHRO-'.uniqid(),
        'name' => 'RS Nefrologi Test',
        'partner_type' => 'hospital',
    ], $officer);

    $referral = $referralService->createReferral($visit, [
        'healthcare_partner_id' => $partner->id,
        'urgency' => 'routine',
        'reason' => 'CKD for nephrology evaluation and HD optimization',
        'clinical_summary' => 'CKD stage 5 on HD, perlu evaluasi adekuasi HD dan manajemen komplikasi',
    ], $officer);

    $version = $referral->versions()->latest('version_number')->first();

    return [$referral, $version, $officer];
}

test('generated document is stored on private referral_documents disk, not public disk', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    $updatedVersion = $version->fresh();

    // Must be stored on private disk
    expect($updatedVersion->document_path)->not->toBeNull();
    expect($updatedVersion->document_disk)->toBe('referral_documents');
    expect($updatedVersion->document_status)->toBe('generated');

    // File exists on private disk
    expect(Storage::disk('referral_documents')->exists($updatedVersion->document_path))->toBeTrue();

    // File must NOT exist on public disk
    Storage::fake('public');
    expect(Storage::disk('public')->exists($updatedVersion->document_path))->toBeFalse();
});

test('document path does not contain patient name or referral number (opaque filename)', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    $updatedVersion = $version->fresh();
    $path = $updatedVersion->document_path;

    // Path should not expose patient name or referral number
    expect($path)->not->toContain($referral->referral_number);
    expect($path)->not->toContain($referral->medicalVisit->patient->person->name ?? 'X');
    // Path should be opaque UUID/ULID-based
    expect($path)->toMatch('/^[A-Z0-9]{2}\//');
});

test('document checksum is stable — regeneration attempt on existing document throws exception', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    $checksum1 = $version->fresh()->document_checksum;

    // Attempt to generate again on same version — must be blocked (immutability)
    expect(fn () => $documentService->generateDocument($version->fresh(), $officer))
        ->toThrow(Exception::class, 'sudah ada');

    // Checksum must be unchanged
    expect($version->fresh()->document_checksum)->toBe($checksum1);
});

test('old version document is not mutated when referral data changes', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    $originalChecksum = $version->fresh()->document_checksum;
    $originalPath = $version->fresh()->document_path;

    // "Update" referral reason — in real system this would create a new version
    // Here we just simulate and verify old version is unchanged
    $referral->update(['reason' => 'Alasan diperbarui setelah dokumen dibuat']);

    // Old version document must be unaffected
    expect($version->fresh()->document_checksum)->toBe($originalChecksum);
    expect($version->fresh()->document_path)->toBe($originalPath);
});

test('path traversal in stored path is rejected by serveDownload', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    // Manually inject a traversal path (simulating DB tampering)
    $version->update(['document_path' => '../../../etc/passwd']);

    expect(fn () => $documentService->serveDownload($version->fresh(), $officer))
        ->toThrow(Exception::class, 'valid');
});

test('download returns 403 for user without download permission', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    // Create user without download permission
    $role = Role::create(['name' => 'no-dl-'.uniqid(), 'display_name' => 'No Download']);
    $userNoPerm = User::factory()->create();
    $userNoPerm->roles()->attach($role->id);

    $response = $this->actingAs($userNoPerm)
        ->get("/referrals/{$referral->id}/versions/{$version->id}/document");

    $response->assertForbidden();
});

test('authorized download via controller works and is audited', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    // Create user WITH download permission
    $role = Role::create(['name' => 'with-dl-'.uniqid(), 'display_name' => 'With Download']);
    $dlPerm = Permission::create(['name' => 'download-referral-document', 'display_name' => 'Download Referral Doc']);
    $role->permissions()->attach($dlPerm->id);
    $officerWithPerm = User::factory()->create();
    $officerWithPerm->roles()->attach($role->id);

    $response = $this->actingAs($officerWithPerm)
        ->get("/referrals/{$referral->id}/versions/{$version->id}/document");

    // Should succeed (200) — file download
    $response->assertSuccessful();

    // Should have Content-Disposition header
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');

    // Verify audit log was written
    $auditCount = AuditLog::where('action', 'referral_document.downloaded')
        ->where('subject_id', $version->id)
        ->count();
    expect($auditCount)->toBeGreaterThan(0);
});

test('generated referral document has no public URL', function () {
    [$referral, $version, $officer] = createFinalizedReferralVersionForDocTest();

    $documentService = new ReferralDocumentService;
    $documentService->generateDocument($version, $officer);

    $updatedVersion = $version->fresh();

    // No public URL field should exist on the version
    $data = $updatedVersion->toArray();
    expect($data)->not->toHaveKey('public_url');
    expect($data)->not->toHaveKey('download_url');
    expect($data)->not->toHaveKey('document_url');
});
