<?php

namespace App\Services;

use App\Models\MedicalVisit;
use App\Models\MedicationAdministration;
use App\Models\MedicationOrder;
use App\Models\MedicationSafetyAcknowledgement;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PatientAllergy;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicationService
{
    /**
     * Create a new medication order for a medical visit.
     * NOTE: Order creation DOES NOT reduce pharmacy stock!
     */
    public function createOrder(MedicalVisit $visit, array $data, ?User $actor = null): MedicationOrder
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($visit, $data, $actor) {
            $medicine = Medicine::where('id', $data['medicine_id'])->where('is_active', true)->firstOrFail();

            // Check active patient allergies
            $activeAllergies = PatientAllergy::where('patient_id', $visit->patient_id)
                ->whereIn('clinical_status', ['active', 'suspected', 'confirmed'])
                ->get();

            $order = MedicationOrder::create([
                'medical_visit_id' => $visit->id,
                'clinical_assessment_id' => $data['clinical_assessment_id'] ?? null,
                'medicine_id' => $medicine->id,
                'dose_value' => $data['dose_value'],
                'dose_unit' => $data['dose_unit'],
                'route' => $data['route'] ?? 'oral',
                'frequency_text' => $data['frequency_text'],
                'instructions' => $data['instructions'] ?? null,
                'start_at' => $data['start_at'] ?? now(),
                'quantity_per_administration' => (int) ($data['quantity_per_administration'] ?? 1),
                'ordered_by_id' => $actor?->id,
                'ordered_at' => now(),
                'status' => 'active',
                'reason_or_indication' => $data['reason_or_indication'] ?? null,
            ]);

            // Record safety acknowledgement if patient has active allergies and user acknowledged
            if ($activeAllergies->isNotEmpty() && ! empty($data['allergy_acknowledgement_reason'])) {
                foreach ($activeAllergies as $allergy) {
                    MedicationSafetyAcknowledgement::create([
                        'patient_id' => $visit->patient_id,
                        'medical_visit_id' => $visit->id,
                        'medication_order_id' => $order->id,
                        'warning_type' => 'active_allergy_warning',
                        'allergy_reference_id' => $allergy->id,
                        'warning_snapshot' => "Pasien memiliki riwayat alergi aktif ({$allergy->allergen}: {$allergy->reaction}). Obat yang diinstruksikan: {$medicine->generic_name}.",
                        'acknowledged_by_id' => $actor?->id,
                        'acknowledged_at' => now(),
                        'reason' => $data['allergy_acknowledgement_reason'],
                    ]);
                }
            }

            AuditLogService::log(
                action: 'medication_order.created',
                subjectType: 'MedicationOrder',
                subjectId: $order->id,
                before: null,
                after: $order->toArray(),
                reason: "Pemberian instruksi obat baru: {$medicine->generic_name} {$order->dose_value} {$order->dose_unit} ({$order->frequency_text})"
            );

            return $order;
        });
    }

    /**
     * Schedule a medication administration entry.
     * NOTE: Scheduled status DOES NOT reduce pharmacy stock!
     */
    public function scheduleAdministration(MedicationOrder $order, array $data, ?User $actor = null): MedicationAdministration
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($order, $data, $actor) {
            $admin = MedicationAdministration::create([
                'medical_visit_id' => $order->medical_visit_id,
                'medication_order_id' => $order->id,
                'medicine_id' => $order->medicine_id,
                'scheduled_at' => $data['scheduled_at'] ?? now(),
                'status' => 'scheduled',
                'dose_value' => $order->dose_value,
                'dose_unit' => $order->dose_unit,
                'route' => $order->route,
                'recorded_at' => now(),
                'recorded_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'medication_administration.scheduled',
                subjectType: 'MedicationAdministration',
                subjectId: $admin->id,
                before: null,
                after: $admin->toArray(),
                reason: 'Penjadwalan pemberian obat kepada santri'
            );

            return $admin;
        });
    }

    /**
     * Administer medication to patient with ATOMIC STOCK ISSUE.
     * Stock is ONLY deducted when status transitions to administered!
     */
    public function administerMedication(MedicationAdministration $admin, MedicineBatch $batch, ?User $actor = null): MedicationAdministration
    {
        $actor = $actor ?? Auth::user();

        if ($admin->status === 'administered') {
            throw new Exception("Pemberian obat {$admin->id} sudah dicatat sebelumnya.");
        }

        return DB::transaction(function () use ($admin, $batch, $actor) {
            // Lock batch and medicine
            $lockedBatch = MedicineBatch::where('id', $batch->id)->lockForUpdate()->firstOrFail();
            $medicine = Medicine::where('id', $lockedBatch->medicine_id)->lockForUpdate()->firstOrFail();
            /** @var MedicalVisit $visit */
            $visit = $admin->medicalVisit;
            /** @var MedicationOrder|null $order */
            $order = $admin->medicationOrder;

            if ($lockedBatch->medicine_id !== $admin->medicine_id) {
                throw new Exception('Batch obat yang dipilih tidak sesuai dengan jenis obat pada instruksi pemberian.');
            }

            if ($lockedBatch->status !== 'active' || $lockedBatch->isExpired()) {
                throw new Exception("Batch obat {$lockedBatch->batch_number} sudah tidak aktif atau kedaluwarsa.");
            }

            $qtyToDeduct = $order ? $order->quantity_per_administration : 1;

            if ($lockedBatch->current_quantity < $qtyToDeduct) {
                throw new Exception("Stok batch {$lockedBatch->batch_number} tidak mencukupi ({$lockedBatch->current_quantity} {$medicine->base_unit} tersedia, dibutuhkan {$qtyToDeduct}).");
            }

            // ATOMIC STOCK ISSUE LEDGER CREATION
            $movement = StockMovement::create([
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $lockedBatch->id,
                'stock_location_id' => $lockedBatch->stock_location_id,
                'movement_type' => 'medication_administration_issue',
                'quantity' => $qtyToDeduct,
                'unit' => $medicine->base_unit,
                'occurred_at' => now(),
                'recorded_by_id' => $actor?->id,
                'reason' => "Pengeluaran obat untuk pemberian pasien pada kunjungan {$visit->visit_number}",
                'reference_type' => 'MedicationAdministration',
                'reference_id' => $admin->id,
            ]);

            // Deduct stock balance
            $lockedBatch->decrement('current_quantity', $qtyToDeduct);
            if ($lockedBatch->fresh()->current_quantity === 0) {
                $lockedBatch->update(['status' => 'depleted']);
            }

            // Update administration record
            $admin->update([
                'medicine_batch_id' => $lockedBatch->id,
                'status' => 'administered',
                'administered_at' => now(),
                'administered_by_id' => $actor?->id,
                'stock_movement_id' => $movement->id,
            ]);

            AuditLogService::log(
                action: 'medication_administration.administered',
                subjectType: 'MedicationAdministration',
                subjectId: $admin->id,
                before: ['status' => 'scheduled'],
                after: ['status' => 'administered', 'batch_number' => $lockedBatch->batch_number, 'stock_movement_id' => $movement->id],
                reason: "Pemberian obat kepada santri selesai & stok batch {$lockedBatch->batch_number} terkurangi {$qtyToDeduct} {$medicine->base_unit}"
            );

            return $admin;
        });
    }

    /**
     * Record non-administered status (held, refused, missed, cancelled).
     * NOTE: These statuses DO NOT reduce pharmacy stock!
     */
    public function recordNonAdministeredStatus(MedicationAdministration $admin, string $status, string $reason, ?User $actor = null): MedicationAdministration
    {
        $actor = $actor ?? Auth::user();

        if (empty(trim($reason))) {
            throw new Exception('Alasan status tidak diberikan (held/refused/missed/cancelled) wajib diisi.');
        }

        return DB::transaction(function () use ($admin, $status, $reason, $actor) {
            $admin->update([
                'status' => $status,
                'reason' => $reason,
                'recorded_at' => now(),
                'recorded_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: "medication_administration.{$status}",
                subjectType: 'MedicationAdministration',
                subjectId: $admin->id,
                before: ['status' => $admin->getOriginal('status')],
                after: ['status' => $status, 'reason' => $reason],
                reason: "Pemberian obat tidak dilaksanakan (status: {$status}): {$reason}"
            );

            return $admin;
        });
    }

    /**
     * Correct an administered record by setting entered_in_error and ATOMICALLY REVERSING stock issue.
     */
    public function correctAdministration(MedicationAdministration $admin, string $reason, ?User $actor = null): MedicationAdministration
    {
        $actor = $actor ?? Auth::user();

        if (empty(trim($reason))) {
            throw new Exception('Alasan pembatalan (entered_in_error) pemberian obat wajib diisi.');
        }

        if ($admin->status !== 'administered') {
            throw new Exception("Hanya pemberian obat berstatus 'administered' yang dapat dibatalkan mutasi stoknya.");
        }

        return DB::transaction(function () use ($admin, $reason, $actor) {
            $lockedAdmin = MedicationAdministration::where('id', $admin->id)->lockForUpdate()->firstOrFail();
            $batch = MedicineBatch::where('id', $lockedAdmin->medicine_batch_id)->lockForUpdate()->firstOrFail();
            $medicine = Medicine::where('id', $lockedAdmin->medicine_id)->lockForUpdate()->firstOrFail();
            /** @var MedicationOrder|null $order */
            $order = $lockedAdmin->medicationOrder;

            $qtyToRestore = $order ? $order->quantity_per_administration : 1;

            // Create reversal stock movement entry
            $reversalMovement = StockMovement::create([
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch->id,
                'stock_location_id' => $batch->stock_location_id,
                'movement_type' => 'medication_administration_reversal',
                'quantity' => $qtyToRestore,
                'unit' => $medicine->base_unit,
                'occurred_at' => now(),
                'recorded_by_id' => $actor?->id,
                'reason' => "Pembatalan pemberian obat (entered_in_error): {$reason}",
                'reverses_movement_id' => $lockedAdmin->stock_movement_id,
                'reference_type' => 'MedicationAdministration',
                'reference_id' => $lockedAdmin->id,
            ]);

            // Restore batch quantity
            $batch->increment('current_quantity', $qtyToRestore);
            $batch->update(['status' => 'active']);

            // Mark administration as entered_in_error
            $lockedAdmin->update([
                'status' => 'entered_in_error',
                'notes' => "Dibatalkan (entered_in_error): {$reason}",
            ]);

            AuditLogService::log(
                action: 'medication_administration.entered_in_error',
                subjectType: 'MedicationAdministration',
                subjectId: $lockedAdmin->id,
                before: ['status' => 'administered'],
                after: ['status' => 'entered_in_error', 'reversal_movement_id' => $reversalMovement->id],
                reason: "Pembatalan catatan pemberian obat & pengembalian stok batch {$batch->batch_number} sejumlah {$qtyToRestore} {$medicine->base_unit}"
            );

            return $lockedAdmin;
        });
    }
}
