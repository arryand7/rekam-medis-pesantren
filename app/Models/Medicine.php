<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'code',
        'generic_name',
        'brand_name',
        'dosage_form',
        'strength_text',
        'base_unit',
        'category',
        'description',
        'minimum_stock',
        'is_active',
        'requires_batch_tracking',
        'requires_expiry_tracking',
        'created_by_id',
        'updated_by_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_batch_tracking' => 'boolean',
            'requires_expiry_tracking' => 'boolean',
            'minimum_stock' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'medicine_id');
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->batches()->where('status', 'active')->sum('current_quantity');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->minimum_stock;
    }
}
