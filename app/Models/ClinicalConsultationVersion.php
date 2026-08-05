<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalConsultationVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'clinical_consultation_id',
        'version_number',
        'summary_payload',
        'checksum',
        'authored_by_id',
        'finalized_at',
        'supersedes_version_id',
        'redaction_notes',
    ];

    protected function casts(): array
    {
        return [
            'summary_payload' => 'array',
            'version_number' => 'integer',
            'finalized_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function authoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authored_by_id');
    }

    public function supersedesVersion(): BelongsTo
    {
        return $this->belongsTo(ClinicalConsultationVersion::class, 'supersedes_version_id');
    }
}
