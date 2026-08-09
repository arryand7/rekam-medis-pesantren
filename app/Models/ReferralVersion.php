<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Immutable versioned snapshot of a referral document.
 *
 * Once finalized, no fields may be overwritten.
 * Private document files are stored on the `referral_documents` disk only.
 * No public URL is ever generated for these documents.
 *
 * @property string $id
 * @property string $referral_id
 * @property int $version_number
 * @property array<string, mixed> $summary_payload
 * @property string $checksum
 * @property string|null $authored_by_id
 * @property Carbon|null $finalized_at
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
 */
class ReferralVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'referral_id',
        'version_number',
        'summary_payload',
        'checksum',
        'authored_by_id',
        'finalized_at',
        'supersedes_version_id',
        'redaction_notes',
        // Private document fields — populated by ReferralDocumentService only
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
            'summary_payload' => 'array',
            'version_number' => 'integer',
            'finalized_at' => 'datetime',
            'generated_at' => 'datetime',
            'document_size' => 'integer',
        ];
    }

    public function hasDocument(): bool
    {
        return $this->document_status === 'generated' && $this->document_path !== null;
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function authoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authored_by_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }
}
