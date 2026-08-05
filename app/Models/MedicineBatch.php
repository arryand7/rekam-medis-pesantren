<?php

namespace App\Models;

use Carbon\CarbonInterface;
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
        return $this->expiry_date instanceof CarbonInterface && $this->expiry_date->isPast();
    }

    public function isNearExpiry(int $daysThreshold = 30): bool
    {
        return $this->expiry_date instanceof CarbonInterface
            && ! $this->isExpired()
            && $this->expiry_date->diffInDays(now()) <= $daysThreshold;
    }
}
