<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $person_id
 * @property string $destination
 * @property string $conflict_type
 * @property array<string, mixed> $source_identifier_snapshot
 * @property string $status
 * @property string|null $resolution_notes
 * @property string|null $resolved_by_id
 * @property Carbon|null $resolved_at
 * @property string $correlation_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class IntegrationIdentityConflict extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'person_id',
        'destination',
        'conflict_type',
        'source_identifier_snapshot',
        'status',
        'resolution_notes',
        'resolved_by_id',
        'resolved_at',
        'correlation_id',
    ];

    protected $casts = [
        'source_identifier_snapshot' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
