<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Private referral document service.
 *
 * Security invariants:
 * - Documents ALWAYS stored on 'referral_documents' disk (private, outside public/)
 * - No public URL is ever generated
 * - Filenames are opaque (ULID-based), never include patient name or referral number
 * - Every download is audited with correlation ID
 * - File content is SHA-256 checksummed
 * - Once generated, a version's document_path is never overwritten (immutability)
 * - New content must create a new version
 */
class ReferralDocumentService
{
    private const DISK = 'referral_documents';

    /**
     * Generate a plain-text summary document for a finalized referral version.
     *
     * This is a stub implementation producing plain text.
     * A full implementation would produce a structured PDF (e.g. via Browsershot or DomPDF)
     * ensuring content is light-mode and minimum-necessary.
     *
     * Invariant: if document_path already exists for this version, throws to prevent overwrite.
     */
    public function generateDocument(ReferralVersion $version, ?User $actor = null): ReferralVersion
    {
        $actor = $actor ?? Auth::user();

        if ($version->document_path !== null) {
            throw new Exception('Dokumen untuk versi ini sudah ada. Buat versi baru untuk menghasilkan dokumen baru.');
        }

        $version->update(['document_status' => 'generating']);

        try {
            $payload = $version->summary_payload;
            $referralNumber = isset($payload['referral_number']) && is_string($payload['referral_number']) ? $payload['referral_number'] : 'UNKNOWN';
            $urgency = strtoupper(isset($payload['urgency']) && is_string($payload['urgency']) ? $payload['urgency'] : 'ROUTINE');

            $content = $this->buildDocumentContent($referralNumber, $urgency, $payload, $version);

            // Opaque filename — never expose patient name or referral number in path
            $filename = (string) Str::ulid().'.txt';
            $directory = substr($version->id, 0, 2); // sharding by first 2 chars of ULID
            $relativePath = $directory.'/'.$filename;

            Storage::disk(self::DISK)->put($relativePath, $content);

            $fileChecksum = hash('sha256', $content);
            $fileSize = strlen($content);

            $version->update([
                'document_path' => $relativePath,
                'document_disk' => self::DISK,
                'document_mime' => 'text/plain',
                'document_size' => $fileSize,
                'document_checksum' => $fileChecksum,
                'document_status' => 'generated',
                'generated_at' => now(),
                'generated_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'referral_document.generated',
                subjectType: 'ReferralVersion',
                subjectId: $version->id,
                before: null,
                after: ['document_status' => 'generated', 'checksum' => $fileChecksum],
                reason: 'Pembuatan dokumen rujukan versi '.$version->version_number
            );

        } catch (\Throwable $e) {
            $version->update(['document_status' => 'generation_failed']);
            throw new Exception('Gagal menghasilkan dokumen rujukan: '.$e->getMessage(), 0, $e);
        }

        return $version->refresh();
    }

    /**
     * Serve a private referral document download.
     *
     * Authorization must be checked BEFORE calling this method (in the Controller).
     *
     * Security:
     * - Path traversal protection: only paths stored in database are served
     * - File existence check before serving
     * - No raw storage path in response headers
     * - Safe Content-Disposition filename (no patient name)
     * - Download is audited with correlation ID
     */
    public function serveDownload(ReferralVersion $version, ?User $actor = null): StreamedResponse
    {
        $actor = $actor ?? Auth::user();

        if (! $version->hasDocument()) {
            throw new Exception('Dokumen rujukan belum tersedia atau belum selesai dibuat.');
        }

        // Path traversal protection: use only the DB-stored path, never client input
        $storedPath = $version->document_path;

        // Validate stored path is within expected pattern (no ../ traversal)
        if (str_contains($storedPath, '..') || str_contains($storedPath, "\0")) {
            throw new Exception('Path dokumen tidak valid.');
        }

        $disk = Storage::disk($version->document_disk ?? self::DISK);

        if (! $disk->exists($storedPath)) {
            throw new Exception('File dokumen tidak ditemukan di storage.');
        }

        $correlationId = (string) Str::uuid();

        AuditLogService::log(
            action: 'referral_document.downloaded',
            subjectType: 'ReferralVersion',
            subjectId: $version->id,
            before: null,
            after: [
                'correlation_id' => $correlationId,
                'downloaded_by' => $actor?->id,
                'downloaded_at' => now()->toIso8601String(),
            ],
            reason: 'Unduhan dokumen rujukan versi '.$version->version_number
        );

        // Safe filename — opaque, no patient data
        $safeFilename = 'referral-doc-v'.$version->version_number.'.txt';

        return $disk->download($storedPath, $safeFilename, [
            'Content-Type' => $version->document_mime ?? 'application/octet-stream',
            'X-Correlation-Id' => $correlationId,
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Build plain-text document content (minimum-necessary).
     * A production implementation should use a PDF library.
     */
    private function buildDocumentContent(
        string $referralNumber,
        string $urgency,
        array $payload,
        ReferralVersion $version
    ): string {
        $lines = [];
        $lines[] = '========================================';
        $lines[] = 'SURAT RUJUKAN PASIEN — SABIRA POSKESTREN';
        $lines[] = '========================================';
        $lines[] = '';
        $lines[] = 'Nomor Rujukan  : '.$referralNumber;
        $lines[] = 'Versi Dokumen  : '.$version->version_number;
        $lines[] = 'Urgensi        : '.$urgency;
        $lines[] = 'Tanggal/Waktu  : '.($version->finalized_at !== null ? $version->finalized_at->format('d F Y H:i') : '-');
        $lines[] = '';
        $lines[] = '--- IDENTITAS PASIEN ---';
        $patient = $payload['patient'] ?? [];
        $lines[] = 'Nomor Pasien   : '.($patient['patient_number'] ?? '-');
        $lines[] = 'Nama           : '.($patient['name'] ?? '-');
        $lines[] = 'Jenis Kelamin  : '.($patient['gender'] ?? '-');
        $lines[] = '';
        $lines[] = '--- INFORMASI KUNJUNGAN ---';
        $visit = $payload['visit'] ?? [];
        $lines[] = 'No. Kunjungan  : '.($visit['visit_number'] ?? '-');
        $lines[] = 'Keluhan Utama  : '.($visit['chief_complaint'] ?? '-');
        $lines[] = '';
        $lines[] = '--- ALASAN RUJUKAN ---';
        $lines[] = $payload['reason'] ?? '-';
        $lines[] = '';
        $lines[] = '--- RINGKASAN KLINIS ---';
        $lines[] = $payload['clinical_summary'] ?? '-';
        $lines[] = '';
        $lines[] = '--- PENILAIAN KLINIS ---';
        $lines[] = 'Diagnosis Kerja : '.($payload['working_diagnosis'] ?? '-');
        $lines[] = 'Ringkasan       : '.($payload['assessment_summary'] ?? '-');
        $lines[] = '';
        $lines[] = '--- TUJUAN RUJUKAN ---';
        $lines[] = 'Faskes Tujuan  : '.($payload['destination_partner'] ?? '-');
        $lines[] = 'Layanan/Poli   : '.($payload['requested_service'] ?? '-');
        $lines[] = '';
        $lines[] = '--- INTEGRITAS DOKUMEN ---';
        $lines[] = 'Checksum Payload: '.$version->checksum;
        $lines[] = '';
        $lines[] = 'Dokumen ini dihasilkan secara otomatis oleh sistem SABIRA POSKESTREN.';
        $lines[] = 'Tidak untuk dipublikasikan atau disebarkan tanpa izin.';
        $lines[] = '========================================';

        return implode("\n", $lines);
    }
}
