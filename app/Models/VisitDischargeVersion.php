<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Immutable versioned snapshot of a clinical discharge summary.
 *
 * @property string $id
 * @property string $visit_discharge_id
 * @property int $version_number
 * @property array<string, mixed> $summary_payload
 * @property string $checksum
 * @property string|null $authored_by_id
 * @property Carbon $finalized_at
 * @property string|null $supersedes_version_id
 * @property string|null $redaction_notes
 * @property string|null $document_path
 * @property string $document_disk
 * @property string|null $document_mime
 * @property int|null $document_size
 * @property string|null $document_checksum
 * @property string $document_status
 * @property Carbon|null $generated_at
 * @property string|null $generated_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class VisitDischargeVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'visit_discharge_id',
        'version_number',
        'summary_payload',
        'checksum',
        'authored_by_id',
        'finalized_at',
        'supersedes_version_id',
        'redaction_notes',
        'document_path',
        'document_disk',
        'document_mime',
        'document_size',
        'document_checksum',
        'document_status',
        'generated_at',
        'generated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'summary_payload' => 'array',
            'finalized_at' => 'datetime',
            'document_size' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function visitDischarge(): BelongsTo
    {
        return $this->belongsTo(VisitDischarge::class, 'visit_discharge_id');
    }

    public function authoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authored_by_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function supersedesVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_version_id');
    }

    public function hasDocument(): bool
    {
        return $this->document_status === 'generated' && $this->document_path !== null;
    }
}
