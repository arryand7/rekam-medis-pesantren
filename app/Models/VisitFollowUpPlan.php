<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Follow-up care and re-evaluation plan for a discharged medical visit.
 *
 * @property string $id
 * @property string $visit_discharge_id
 * @property string $follow_up_type
 * @property Carbon|null $due_at
 * @property string|null $healthcare_partner_id
 * @property string $instructions
 * @property string|null $responsible_party_type
 * @property string|null $responsible_party_reference
 * @property string $status
 * @property string $created_by_id
 * @property Carbon|null $completed_at
 * @property string|null $completed_by_id
 * @property string|null $cancellation_reason
 * @property string|null $notes
 * @property int $lock_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class VisitFollowUpPlan extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'visit_discharge_id',
        'follow_up_type',
        'due_at',
        'healthcare_partner_id',
        'instructions',
        'responsible_party_type',
        'responsible_party_reference',
        'status',
        'created_by_id',
        'completed_at',
        'completed_by_id',
        'cancellation_reason',
        'notes',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function visitDischarge(): BelongsTo
    {
        return $this->belongsTo(VisitDischarge::class, 'visit_discharge_id');
    }

    public function healthcarePartner(): BelongsTo
    {
        return $this->belongsTo(HealthcarePartner::class, 'healthcare_partner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'planned';
    }
}
