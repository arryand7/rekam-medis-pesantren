<?php

namespace App\Services;

use App\Models\MedicalVisit;
use App\Models\User;
use App\Models\VitalSign;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VitalSignService
{
    /**
     * Record a new vital sign measurement (Draft or Finalized).
     */
    public function record(MedicalVisit $visit, array $data, ?User $actor = null): VitalSign
    {
        $actor = $actor ?? Auth::user();

        // Validation of physical bounds
        $this->validateMeasurements($data);

        return DB::transaction(function () use ($visit, $data, $actor) {
            $isFinal = ! empty($data['finalize']) && $data['finalize'] === true;

            $vital = VitalSign::create([
                'medical_visit_id' => $visit->id,
                'recorded_at' => now(),
                'recorded_by_id' => $actor?->id,
                'temperature_c' => $data['temperature_c'] ?? null,
                'systolic_bp' => $data['systolic_bp'] ?? null,
                'diastolic_bp' => $data['diastolic_bp'] ?? null,
                'pulse_bpm' => $data['pulse_bpm'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'spo2_percent' => $data['spo2_percent'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'height_cm' => $data['height_cm'] ?? null,
                'pain_score' => $data['pain_score'] ?? null,
                'consciousness_level' => $data['consciousness_level'] ?? 'compos_mentis',
                'notes' => $data['notes'] ?? null,
                'status' => $isFinal ? 'finalized' : 'draft',
                'finalized_at' => $isFinal ? now() : null,
                'finalized_by_id' => $isFinal ? $actor?->id : null,
            ]);

            AuditLogService::log(
                action: $isFinal ? 'vital_signs.finalized' : 'vital_signs.recorded',
                subjectType: 'VitalSign',
                subjectId: $vital->id,
                before: null,
                after: $vital->toArray(),
                reason: $isFinal ? 'Pencatatan dan finalisasi tanda vital' : 'Draft pencatatan tanda vital'
            );

            return $vital;
        });
    }

    /**
     * Finalize a draft vital sign record.
     */
    public function finalize(VitalSign $vital, ?User $actor = null): VitalSign
    {
        $actor = $actor ?? Auth::user();

        if ($vital->status === 'finalized') {
            throw new Exception("Data tanda vital {$vital->id} sudah difinalisasi sebelumnya.");
        }

        return DB::transaction(function () use ($vital, $actor) {
            $vital->update([
                'status' => 'finalized',
                'finalized_at' => now(),
                'finalized_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'vital_signs.finalized',
                subjectType: 'VitalSign',
                subjectId: $vital->id,
                before: ['status' => 'draft'],
                after: ['status' => 'finalized'],
                reason: 'Finalisasi data tanda vital'
            );

            return $vital;
        });
    }

    /**
     * Mark vital sign record as entered-in-error (Non-destructive).
     */
    public function markAsError(VitalSign $vital, string $reason, ?User $actor = null): VitalSign
    {
        return DB::transaction(function () use ($vital, $reason) {
            $beforeStatus = $vital->status;

            $vital->update([
                'status' => 'entered_in_error',
            ]);

            AuditLogService::log(
                action: 'vital_signs.entered_in_error',
                subjectType: 'VitalSign',
                subjectId: $vital->id,
                before: ['status' => $beforeStatus],
                after: ['status' => 'entered_in_error'],
                reason: $reason
            );

            return $vital;
        });
    }

    protected function validateMeasurements(array $data): void
    {
        if (isset($data['temperature_c']) && ($data['temperature_c'] < 30.0 || $data['temperature_c'] > 45.0)) {
            throw new Exception('Suhu tubuh di luar rentang fisiologis normal (30°C - 45°C).');
        }

        if (isset($data['spo2_percent']) && ($data['spo2_percent'] < 50 || $data['spo2_percent'] > 100)) {
            throw new Exception('Saturasi O2 (SpO2) di luar rentang valid (50% - 100%).');
        }
    }
}
