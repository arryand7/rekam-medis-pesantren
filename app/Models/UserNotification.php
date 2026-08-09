<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $notification_type
 * @property string $title
 * @property string $body
 * @property array<string, mixed>|null $payload_snapshot
 * @property string|null $source_type
 * @property string|null $source_id
 * @property Carbon|null $read_at
 * @property Carbon|null $acknowledged_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserNotification extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'notification_type',
        'title',
        'body',
        'payload_snapshot',
        'source_type',
        'source_id',
        'read_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }
}
