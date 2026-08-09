<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateIdentityMapping extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'gate_user_id',
        'person_id',
        'mapping_method',
        'confidence_score',
        'status',
        'notes',
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
