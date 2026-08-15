<?php

namespace App\Queries\Pharmacy;

use App\Models\MedicineBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryBatchQuery
{
    /**
     * @param  array{search?: string|null, condition?: string|null, location?: string|null}  $filters
     * @return LengthAwarePaginator<int, MedicineBatch>
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = MedicineBatch::query()
            ->with(['medicine', 'location'])
            ->where('status', '!=', 'entered_in_error');

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $pattern = '%'.$search.'%';

            $query->where(function (Builder $batchQuery) use ($pattern): void {
                $batchQuery->where('batch_number', 'like', $pattern)
                    ->orWhereHas('medicine', function (Builder $medicineQuery) use ($pattern): void {
                        $medicineQuery->where('generic_name', 'like', $pattern)
                            ->orWhere('brand_name', 'like', $pattern)
                            ->orWhere('code', 'like', $pattern);
                    })
                    ->orWhereHas('location', function (Builder $locationQuery) use ($pattern): void {
                        $locationQuery->where('name', 'like', $pattern)
                            ->orWhere('code', 'like', $pattern);
                    });
            });
        }

        if (! empty($filters['location'])) {
            $query->where('stock_location_id', $filters['location']);
        }

        $this->applyCondition($query, $filters['condition'] ?? null);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /** @param Builder<MedicineBatch> $query */
    private function applyCondition(Builder $query, ?string $condition): void
    {
        match ($condition) {
            'available' => $query
                ->where('status', 'active')
                ->where('current_quantity', '>', 0)
                ->where(function (Builder $expiryQuery): void {
                    $warningLimit = now()
                        ->addDays((int) config('pharmacy.expiry_warning_days', 30))
                        ->toDateString();

                    $expiryQuery->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>', $warningLimit);
                }),
            'near_expiry' => $query->nearExpiry(),
            'expired' => $query->expired(),
            'depleted' => $query->depleted(),
            default => null,
        };
    }
}
