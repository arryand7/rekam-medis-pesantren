<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateSyncRun extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'run_type',
        'status',
        'total_records',
        'applied_count',
        'failed_count',
        'conflict_count',
        'summary_json',
        'source_version_cursor',
        'executed_by_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'summary_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by_id');
    }
}
