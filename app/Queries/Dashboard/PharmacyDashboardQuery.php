<?php

namespace App\Queries\Dashboard;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PharmacyDashboardQuery
{
    /**
     * Get aggregate KPI metrics for pharmacy dashboard.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(?Carbon $date = null): array
    {
        $targetDate = $date ?? now();
        $today = $targetDate->copy()->toDateString();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();
        $warningDays = (int) config('pharmacy.expiry_warning_days', 30);
        $nearExpiryThreshold = $targetDate->copy()->addDays($warningDays)->toDateString();
        $rawLowStock = config('pharmacy.low_stock_threshold');
        $lowStockConfigured = $rawLowStock !== null;
        $lowStockThreshold = $lowStockConfigured ? (int) $rawLowStock : null;

        $expiredCount = MedicineBatch::where('expiry_date', '<', $today)
            ->where('current_quantity', '>', 0)
            ->count();

        $nearExpiryCount = MedicineBatch::where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $nearExpiryThreshold)
            ->where('current_quantity', '>', 0)
            ->count();

        $depletedCount = MedicineBatch::where('current_quantity', '<=', 0)->count();

        $lowStockMedicinesCount = null;
        if ($lowStockConfigured && $lowStockThreshold !== null) {
            $thresholdVal = $lowStockThreshold;
            $lowStockMedicinesCount = Medicine::where('is_active', true)
                ->whereHas('batches', function ($query) use ($thresholdVal) {
                    $query->where('current_quantity', '<=', $thresholdVal);
                })
                ->count();
        }

        $movementsToday = StockMovement::whereBetween('created_at', [$startOfDay, $endOfDay])->count();

        $dispensesToday = StockMovement::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereIn('movement_type', ['dispense', 'adjustment_out'])
            ->count();

        $adjustmentsToday = StockMovement::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereIn('movement_type', ['adjustment_in', 'adjustment_out', 'reversal'])
            ->count();

        return [
            'expired_batches' => $expiredCount,
            'near_expiry_batches' => $nearExpiryCount,
            'depleted_batches' => $depletedCount,
            'low_stock_medicines' => $lowStockMedicinesCount,
            'low_stock_configured' => $lowStockConfigured,
            'movements_today' => $movementsToday,
            'dispenses_today' => $dispensesToday,
            'adjustments_today' => $adjustmentsToday,
            'warning_days_window' => $warningDays,
            'low_stock_threshold' => $lowStockThreshold,
        ];
    }

    /**
     * List of expired and near-expiry batches requiring action.
     */
    public function getExpiringBatches(int $limit = 15): Collection
    {
        $warningDays = (int) config('pharmacy.expiry_warning_days', 30);
        $threshold = now()->addDays($warningDays)->toDateString();

        return MedicineBatch::with(['medicine', 'location'])
            ->where('expiry_date', '<=', $threshold)
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function (MedicineBatch $batch): array {
                $isExpired = $batch->expiry_date ? $batch->expiry_date->isPast() : false;
                $daysRemaining = $batch->expiry_date ? (int) now()->diffInDays($batch->expiry_date, false) : 0;
                /** @var Medicine|null $medicine */
                $medicine = $batch->medicine;
                /** @var StockLocation|null $location */
                $location = $batch->location;

                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'medicine_name' => $medicine ? ($medicine->brand_name ?? $medicine->generic_name) : 'Obat',
                    'form' => $medicine ? ($medicine->dosage_form ?? 'Tablet/Kapsul') : 'Tablet/Kapsul',
                    'location' => $location ? $location->name : 'Apotek Utama',
                    'current_quantity' => $batch->current_quantity,
                    'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('d M Y') : '-',
                    'is_expired' => $isExpired,
                    'days_remaining' => $daysRemaining,
                    'status_label' => $isExpired ? 'Kedaluwarsa' : ($daysRemaining <= 7 ? 'Sangat Kritis' : 'Hampir Kedaluwarsa'),
                ];
            });
    }

    /**
     * List of depleted or low-stock medicines.
     */
    public function getDepletedMedicines(int $limit = 15): Collection
    {
        return MedicineBatch::with(['medicine', 'location'])
            ->where('current_quantity', '<=', 0)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (MedicineBatch $batch): array {
                /** @var Medicine|null $medicine */
                $medicine = $batch->medicine;
                /** @var StockLocation|null $location */
                $location = $batch->location;

                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'medicine_name' => $medicine ? ($medicine->brand_name ?? $medicine->generic_name) : 'Obat',
                    'code' => $medicine ? ($medicine->code ?? '-') : '-',
                    'location' => $location ? $location->name : 'Apotek Utama',
                    'last_updated' => $batch->updated_at ? $batch->updated_at->format('d M Y H:i') : '-',
                ];
            });
    }

    /**
     * Recent ledger movements (audit trail snippet).
     */
    public function getRecentMovements(int $limit = 15): Collection
    {
        return StockMovement::with(['medicine', 'batch', 'recordedBy'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (StockMovement $mov): array {
                /** @var Medicine|null $medicine */
                $medicine = $mov->medicine;
                /** @var MedicineBatch|null $batch */
                $batch = $mov->batch;
                /** @var User|null $recordedBy */
                $recordedBy = $mov->recordedBy;

                return [
                    'id' => $mov->id,
                    'created_at' => $mov->created_at ? $mov->created_at->format('d M Y H:i') : '-',
                    'medicine_name' => $medicine ? ($medicine->brand_name ?? $medicine->generic_name) : 'Obat',
                    'batch_number' => $batch ? $batch->batch_number : '-',
                    'movement_type' => $mov->movement_type,
                    'quantity_change' => $mov->quantity,
                    'balance_after' => $batch ? $batch->current_quantity : 0,
                    'reference_type' => $mov->reference_type,
                    'actor_name' => $recordedBy ? $recordedBy->name : 'Sistem',
                    'notes' => $mov->reason,
                ];
            });
    }
}
