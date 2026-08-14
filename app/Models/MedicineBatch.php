<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonInterface|null $expiry_date
 */
class MedicineBatch extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medicine_id',
        'stock_location_id',
        'batch_number',
        'expiry_date',
        'received_at',
        'supplier_name',
        'purchase_reference',
        'initial_quantity',
        'current_quantity',
        'status', // active, depleted, expired, quarantined, recalled, entered_in_error
        'created_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'medicine_batch_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date instanceof CarbonInterface && $this->expiry_date->lt(now()->startOfDay());
    }

    public function isNearExpiry(?int $daysThreshold = null): bool
    {
        $threshold = $daysThreshold ?? (int) config('pharmacy.expiry_warning_days', 30);

        if (! ($this->expiry_date instanceof CarbonInterface)) {
            return false;
        }

        $today = now()->startOfDay();
        $limit = now()->addDays($threshold)->endOfDay();

        return $this->expiry_date->gte($today) && $this->expiry_date->lte($limit);
    }

    /**
     * @param  Builder<MedicineBatch>  $query
     * @return Builder<MedicineBatch>
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString())
            ->where('current_quantity', '>', 0);
    }

    /**
     * @param  Builder<MedicineBatch>  $query
     * @return Builder<MedicineBatch>
     */
    public function scopeNearExpiry($query, ?int $daysThreshold = null)
    {
        $threshold = now()->addDays($daysThreshold ?? (int) config('pharmacy.expiry_warning_days', 30))->toDateString();

        return $query->whereBetween('expiry_date', [now()->toDateString(), $threshold])
            ->where('current_quantity', '>', 0);
    }

    /**
     * @param  Builder<MedicineBatch>  $query
     * @return Builder<MedicineBatch>
     */
    public function scopeNormal($query, ?int $daysThreshold = null)
    {
        $threshold = now()->addDays($daysThreshold ?? (int) config('pharmacy.expiry_warning_days', 30))->toDateString();

        return $query->where('expiry_date', '>', $threshold)
            ->where('current_quantity', '>', 0);
    }

    /**
     * @param  Builder<MedicineBatch>  $query
     * @return Builder<MedicineBatch>
     */
    public function scopeDepleted($query)
    {
        return $query->where('current_quantity', '<=', 0);
    }
}
