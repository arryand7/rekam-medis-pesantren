<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationAdministration extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'medical_visit_id',
        'medication_order_id',
        'medicine_id',
        'medicine_batch_id',
        'scheduled_at',
        'status', // scheduled, administered, held, refused, missed, cancelled, entered_in_error
        'dose_value',
        'dose_unit',
        'route',
        'administered_at',
        'administered_by_id',
        'recorded_at',
        'recorded_by_id',
        'reason',
        'notes',
        'stock_movement_id',
        'idempotency_key',
        'parent_administration_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'administered_at' => 'datetime',
            'recorded_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class, 'medical_visit_id');
    }

    public function medicationOrder(): BelongsTo
    {
        return $this->belongsTo(MedicationOrder::class, 'medication_order_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administered_by_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function isAdministered(): bool
    {
        return $this->status === 'administered';
    }
}
