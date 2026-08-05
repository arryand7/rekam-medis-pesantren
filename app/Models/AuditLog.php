<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUlids;

    public $timestamps = false; // Uses created_at only

    protected $fillable = [
        'actor_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'payload_before',
        'payload_after',
        'reason',
        'ip_address',
        'user_agent',
        'correlation_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_before' => 'array',
            'payload_after' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
