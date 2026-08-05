<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    /**
     * Create a new medicine master record.
     */
    public function createMedicine(array $data, ?User $actor = null): Medicine
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            $medicine = Medicine::create([
                'code' => strtoupper($data['code']),
                'generic_name' => $data['generic_name'],
                'brand_name' => $data['brand_name'] ?? null,
                'dosage_form' => $data['dosage_form'] ?? 'tablet',
                'strength_text' => $data['strength_text'] ?? null,
                'base_unit' => strtolower($data['base_unit'] ?? 'tablet'),
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'minimum_stock' => (int) ($data['minimum_stock'] ?? 10),
                'is_active' => true,
                'requires_batch_tracking' => true,
                'requires_expiry_tracking' => true,
                'created_by_id' => $actor?->id,
            ]);

            AuditLogService::log(
                action: 'pharmacy.medicine_created',
                subjectType: 'Medicine',
                subjectId: $medicine->id,
                before: null,
                after: $medicine->toArray(),
                reason: "Pendaftaran master obat baru: {$medicine->generic_name} ({$medicine->code})"
            );

            return $medicine;
        });
    }

    /**
     * Receive new medicine stock into inventory (Penerimaan Stok).
     */
    public function receiveStock(array $data, ?User $actor = null): StockMovement
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            $medicine = Medicine::where('id', $data['medicine_id'])->where('is_active', true)->lockForUpdate()->firstOrFail();
            $location = StockLocation::where('id', $data['stock_location_id'])->where('is_active', true)->lockForUpdate()->firstOrFail();

            $batchNumber = strtoupper(trim($data['batch_number']));
            $quantity = (int) $data['quantity'];

            if ($quantity <= 0) {
                throw new Exception('Jumlah penerimaan stok harus lebih dari 0.');
            }

            // Find or create medicine batch
            $batch = MedicineBatch::where('medicine_id', $medicine->id)
                ->where('stock_location_id', $location->id)
                ->where('batch_number', $batchNumber)
                ->lockForUpdate()
                ->first();

            if ($batch) {
                $batch->increment('current_quantity', $quantity);
                if (! empty($data['expiry_date'])) {
                    $batch->update(['expiry_date' => $data['expiry_date'], 'status' => 'active']);
                } else {
                    $batch->update(['status' => 'active']);
                }
            } else {
                $batch = MedicineBatch::create([
                    'medicine_id' => $medicine->id,
                    'stock_location_id' => $location->id,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'received_at' => now(),
                    'supplier_name' => $data['supplier_name'] ?? null,
                    'purchase_reference' => $data['purchase_reference'] ?? null,
                    'initial_quantity' => $quantity,
                    'current_quantity' => $quantity,
                    'status' => 'active',
                    'created_by_id' => $actor?->id,
                ]);
            }

            // Record append-only stock movement
            $movement = StockMovement::create([
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch->id,
                'stock_location_id' => $location->id,
                'movement_type' => 'receipt',
                'quantity' => $quantity,
                'unit' => $medicine->base_unit,
                'occurred_at' => now(),
                'recorded_by_id' => $actor?->id,
                'reason' => $data['reason'] ?? 'Penerimaan stok obat dari supplier/dinas',
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            AuditLogService::log(
                action: 'pharmacy.stock_received',
                subjectType: 'StockMovement',
                subjectId: $movement->id,
                before: null,
                after: $movement->toArray(),
                reason: "Penerimaan stok {$quantity} {$medicine->base_unit} {$medicine->generic_name} (Batch: {$batchNumber})"
            );

            return $movement;
        });
    }

    /**
     * Adjust stock up or down (Stok Opname / Penyesuaian).
     */
    public function adjustStock(array $data, ?User $actor = null): StockMovement
    {
        $actor = $actor ?? Auth::user();

        return DB::transaction(function () use ($data, $actor) {
            $batch = MedicineBatch::where('id', $data['medicine_batch_id'])->lockForUpdate()->firstOrFail();
            $medicine = Medicine::where('id', $batch->medicine_id)->lockForUpdate()->firstOrFail();

            $type = $data['movement_type']; // adjustment_in, adjustment_out
            $quantity = (int) $data['quantity'];
            $reason = trim($data['reason'] ?? '');

            if (empty($reason)) {
                throw new Exception('Alasan penyesuaian stok wajib diisi.');
            }

            if ($quantity <= 0) {
                throw new Exception('Jumlah penyesuaian harus lebih dari 0.');
            }

            if ($type === 'adjustment_out') {
                // NO NEGATIVE STOCK GUARD
                if ($batch->current_quantity < $quantity) {
                    throw new Exception("Stok tidak mencukupi! Stok saat ini: {$batch->current_quantity} {$medicine->base_unit}, diminta pengeluaran: {$quantity} {$medicine->base_unit}.");
                }

                $batch->decrement('current_quantity', $quantity);

                if ($batch->fresh()->current_quantity === 0) {
                    $batch->update(['status' => 'depleted']);
                }
            } else {
                $batch->increment('current_quantity', $quantity);
                $batch->update(['status' => 'active']);
            }

            $movement = StockMovement::create([
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch->id,
                'stock_location_id' => $batch->stock_location_id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'unit' => $medicine->base_unit,
                'occurred_at' => now(),
                'recorded_by_id' => $actor?->id,
                'reason' => $reason,
            ]);

            AuditLogService::log(
                action: 'pharmacy.stock_adjusted',
                subjectType: 'StockMovement',
                subjectId: $movement->id,
                before: null,
                after: $movement->toArray(),
                reason: "Penyesuaian stok ({$type}): {$quantity} {$medicine->base_unit} — {$reason}"
            );

            return $movement;
        });
    }

    /**
     * Reverse a previous stock movement entry.
     */
    public function reverseMovement(StockMovement $movement, string $reason, ?User $actor = null): StockMovement
    {
        $actor = $actor ?? Auth::user();

        if (empty(trim($reason))) {
            throw new Exception('Alasan pembatalan (reversal) mutasi stok wajib diisi.');
        }

        return DB::transaction(function () use ($movement, $reason, $actor) {
            // Check if already reversed
            $alreadyReversed = StockMovement::where('reverses_movement_id', $movement->id)->exists();
            if ($alreadyReversed) {
                throw new Exception("Mutasi stok {$movement->id} sudah pernah dibatalkan (reversed) sebelumnya.");
            }

            $batch = MedicineBatch::where('id', $movement->medicine_batch_id)->lockForUpdate()->firstOrFail();
            $medicine = Medicine::where('id', $movement->medicine_id)->lockForUpdate()->firstOrFail();

            // Reverse logic: receipt / adjustment_in -> deduct stock; adjustment_out -> add stock
            if (in_array($movement->movement_type, ['receipt', 'adjustment_in', 'transfer_in'])) {
                // Deducting stock
                if ($batch->current_quantity < $movement->quantity) {
                    throw new Exception("Reversal gagal! Stok tersisa ({$batch->current_quantity}) kurang dari jumlah mutasi asal ({$movement->quantity}).");
                }
                $batch->decrement('current_quantity', $movement->quantity);
            } else {
                // Adding back stock
                $batch->increment('current_quantity', $movement->quantity);
                $batch->update(['status' => 'active']);
            }

            $reversalMovement = StockMovement::create([
                'medicine_id' => $medicine->id,
                'medicine_batch_id' => $batch->id,
                'stock_location_id' => $batch->stock_location_id,
                'movement_type' => 'reversal',
                'quantity' => $movement->quantity,
                'unit' => $medicine->base_unit,
                'occurred_at' => now(),
                'recorded_by_id' => $actor?->id,
                'reason' => "Pembatalan mutasi {$movement->id}: {$reason}",
                'reverses_movement_id' => $movement->id,
            ]);

            AuditLogService::log(
                action: 'pharmacy.movement_reversed',
                subjectType: 'StockMovement',
                subjectId: $reversalMovement->id,
                before: ['original_movement_id' => $movement->id],
                after: $reversalMovement->toArray(),
                reason: "Pembatalan mutasi stok: {$reason}"
            );

            return $reversalMovement;
        });
    }
}
