<?php

namespace App\Services;

use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicalVisitService
{
    /**
     * Register a new medical visit intake safely inside a DB transaction with Active Visit Guard.
     *
     * @param  array{
     *   patient_id: string,
     *   chief_complaint: string,
     *   reporting_type?: string,
     *   reporting_name?: string,
     *   origin_location?: string,
     *   assigned_officer_id?: string,
     *   override_active?: bool,
     *   override_reason?: string
     * }  $data
     *
     * @throws Exception
     */
    public function registerVisit(array $data, ?User $actor = null): MedicalVisit
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            // Row lock the patient record to prevent race condition active visits
            $patient = Patient::where('id', $data['patient_id'])->lockForUpdate()->firstOrFail();

            if (! $patient->is_eligible) {
                throw new Exception("Pasien {$patient->patient_number} tidak layak (non-eligible) untuk pelayanan Poskestren.");
            }

            // Active visit guard check
            $activeVisit = MedicalVisit::where('patient_id', $patient->id)
                ->whereIn('status', ['registered', 'waiting_assessment'])
                ->lockForUpdate()
                ->first();

            $isOverride = (bool) ($data['override_active'] ?? false);

            if ($activeVisit !== null) {
                if (! $isOverride) {
                    throw new Exception("Pasien {$patient->patient_number} masih memiliki kunjungan aktif ({$activeVisit->visit_number}). Selesaikan kunjungan lama atau gunakan override ber-alasan.");
                }

                if (empty($data['override_reason'])) {
                    throw new Exception('Alasan override wajib diisi untuk mendaftarkan kunjungan baru di atas kunjungan aktif.');
                }
            }

            // Create new visit with authoritative server timestamp
            $visit = MedicalVisit::create([
                'visit_number' => MedicalVisit::generateVisitNumber(),
                'patient_id' => $patient->id,
                'status' => 'waiting_assessment', // Moves to waiting_assessment upon registration
                'arrived_at' => now(),
                'chief_complaint' => $data['chief_complaint'],
                'reporting_type' => $data['reporting_type'] ?? 'self',
                'reporting_name' => $data['reporting_name'] ?? null,
                'origin_location' => $data['origin_location'] ?? null,
                'receiving_officer_id' => $actor?->id,
                'assigned_officer_id' => $data['assigned_officer_id'] ?? null,
                'created_by_id' => $actor?->id,
            ]);

            // Record audit log
            AuditLogService::log(
                action: $isOverride ? 'medical_visit.active_override' : 'medical_visit.registered',
                subjectType: 'MedicalVisit',
                subjectId: $visit->id,
                before: $activeVisit ? ['active_visit_id' => $activeVisit->id] : null,
                after: [
                    'visit_number' => $visit->visit_number,
                    'patient_id' => $visit->patient_id,
                    'status' => $visit->status,
                    'chief_complaint' => $visit->chief_complaint,
                ],
                reason: $isOverride ? ($data['override_reason'] ?? 'Override kunjungan aktif') : 'Registrasi kunjungan medis awal (intake)'
            );

            return $visit;
        });
    }

    /**
     * Cancel an active medical visit safely with reason.
     */
    public function cancelVisit(MedicalVisit $visit, string $reason, ?User $actor = null): MedicalVisit
    {
        if ($visit->status === 'cancelled') {
            throw new Exception("Kunjungan {$visit->visit_number} sudah dibatalkan sebelumnya.");
        }

        return DB::transaction(function () use ($visit, $reason) {
            $beforeStatus = $visit->status;

            $visit->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            AuditLogService::log(
                action: 'medical_visit.cancelled',
                subjectType: 'MedicalVisit',
                subjectId: $visit->id,
                before: ['status' => $beforeStatus],
                after: ['status' => 'cancelled', 'cancellation_reason' => $reason],
                reason: $reason
            );

            return $visit;
        });
    }
}
