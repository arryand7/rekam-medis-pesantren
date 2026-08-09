<?php

namespace App\Services;

use App\Models\User;
use App\Models\VisitDischargeVersion;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service managing private discharge summary documents.
 *
 * All discharge documents are stored on the private `discharge_documents` disk.
 * They are NEVER accessible via public URLs or web root symlinks.
 * Downloads are strictly authorized, rate-limited, and audited.
 */
class VisitDischargeDocumentService
{
    public const DISK = 'discharge_documents';

    /**
     * Generate the private text/summary document for a finalized discharge version.
     */
    public function generateDocument(VisitDischargeVersion $version, ?User $actor = null): VisitDischargeVersion
    {
        $actor = $actor ?? Auth::user();

        if ($version->document_path !== null) {
            throw new Exception('Dokumen untuk versi kepulangan ini sudah ada. Buat amandemen baru untuk menghasilkan dokumen baru.');
        }

        $version->update(['document_status' => 'generating']);

        try {
            $payload = $version->summary_payload;
            $visitNumber = isset($payload['visit_number']) && is_string($payload['visit_number']) ? $payload['visit_number'] : 'UNKNOWN';

            $content = $this->buildDocumentContent($visitNumber, $payload, $version);

            // Opaque filename — never expose patient name or visit number in path
            $filename = (string) Str::ulid().'.txt';
            $directory = substr($version->id, 0, 2);
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
                action: 'discharge_summary.generated',
                subjectType: 'VisitDischargeVersion',
                subjectId: $version->id,
                before: null,
                after: ['checksum' => $fileChecksum, 'size_bytes' => $fileSize],
                reason: "Dokumen ringkasan kepulangan versi {$version->version_number} dihasilkan"
            );

            return $version->fresh();
        } catch (Exception $e) {
            $version->update(['document_status' => 'generation_failed']);
            throw $e;
        }
    }

    /**
     * Stream a private discharge document with audit and path traversal check.
     */
    public function streamDocument(VisitDischargeVersion $version, ?User $actor = null): StreamedResponse
    {
        $actor = $actor ?? Auth::user();

        if (! $version->hasDocument()) {
            throw new Exception('Dokumen belum dihasilkan atau berkas tidak tersedia.');
        }

        $path = (string) $version->document_path;

        // Path traversal guard
        if (str_contains($path, '..') || str_contains($path, "\0") || str_starts_with($path, '/')) {
            throw new Exception('Akses berkas tidak valid.');
        }

        if (! Storage::disk(self::DISK)->exists($path)) {
            throw new Exception('Berkas dokumen fisik tidak ditemukan di media penyimpanan aman.');
        }

        // Audit download event
        AuditLogService::log(
            action: 'discharge_summary.downloaded',
            subjectType: 'VisitDischargeVersion',
            subjectId: $version->id,
            before: null,
            after: [
                'version_number' => $version->version_number,
                'document_checksum' => $version->document_checksum,
            ],
            reason: 'Dokumen ringkasan kepulangan versi '.$version->version_number.' diunduh oleh '.($actor !== null ? $actor->name : 'Sistem')
        );

        $downloadFilename = 'DISCHARGE-'.$version->version_number.'-'.substr($version->checksum, 0, 8).'.txt';

        return Storage::disk(self::DISK)->download($path, $downloadFilename, [
            'Content-Type' => $version->document_mime ?? 'text/plain',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Build structured plain text content for discharge summary.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function buildDocumentContent(string $visitNumber, array $payload, VisitDischargeVersion $version): string
    {
        $patient = is_array($payload['patient'] ?? null) ? $payload['patient'] : [];
        $assessment = is_array($payload['clinical_assessment'] ?? null) ? $payload['clinical_assessment'] : [];
        $discharge = is_array($payload['discharge'] ?? null) ? $payload['discharge'] : [];

        $lines = [];
        $lines[] = '==================================================';
        $lines[] = 'RINGKASAN KEPULANGAN KLINIS — SABIRA POSKESTREN';
        $lines[] = '==================================================';
        $lines[] = '';
        $lines[] = 'Nomor Kunjungan   : '.$visitNumber;
        $lines[] = 'Versi Dokumen     : '.$version->version_number;
        $lines[] = 'Waktu Kedatangan  : '.($payload['arrived_at'] ?? '-');
        $lines[] = 'Waktu Kepulangan  : '.$version->finalized_at->format('d F Y H:i');

        $lines[] = '';
        $lines[] = '--- IDENTITAS PASIEN ---';
        $lines[] = 'Nomor Pasien      : '.($patient['patient_number'] ?? '-');
        $lines[] = 'Nama Pasien       : '.($patient['name'] ?? '-');
        $lines[] = 'Jenis Kelamin     : '.($patient['gender'] ?? '-');
        $lines[] = '';
        $lines[] = '--- PENGKAJIAN KLINIS ---';
        $lines[] = 'Keluhan Utama     : '.($payload['chief_complaint'] ?? '-');
        $lines[] = 'Diagnosis Kerja   : '.($assessment['working_diagnosis'] ?? '-');
        $lines[] = 'Ringkasan         : '.($assessment['summary'] ?? '-');
        $lines[] = '';
        $lines[] = '--- STATUS KEPULANGAN ---';
        $lines[] = 'Tipe Kepulangan   : '.($discharge['discharge_type'] ?? '-');
        $lines[] = 'Destinasi Tujuan  : '.($discharge['discharge_destination'] ?? '-');
        $lines[] = 'Kondisi Akhir     : '.($discharge['final_condition'] ?? '-');
        $lines[] = 'Ringkasan Pulang  : '.($discharge['clinical_summary'] ?? '-');
        $lines[] = '';
        $lines[] = '--- REKOMENDASI AKTIVITAS & ISTIRAHAT ---';
        $lines[] = 'Status Aktivitas  : '.($discharge['activity_recommendation'] ?? '-');
        $lines[] = 'Anjuran Istirahat : '.($discharge['rest_recommendation'] ?? '-');
        $lines[] = 'Catatan Batasan   : '.($discharge['restriction_notes'] ?? '-');
        $lines[] = '';
        $lines[] = '--- TINDAK LANJUT (FOLLOW-UP) ---';
        $lines[] = 'Perlu Follow-up   : '.(! empty($discharge['follow_up_required']) ? 'YA' : 'TIDAK');
        $lines[] = 'Rencana Kontrol   : '.($discharge['follow_up_summary'] ?? '-');
        $lines[] = 'Tanggal Kontrol   : '.($discharge['follow_up_date'] ?? '-');
        $lines[] = '';
        $lines[] = '--- INTEGRITAS DOKUMEN ---';
        $lines[] = 'Checksum Payload  : '.$version->checksum;
        $lines[] = 'Dokumen ini dibuat otomatis dan bersifat privat.';
        $lines[] = '==================================================';

        return implode("\n", $lines);
    }
}
