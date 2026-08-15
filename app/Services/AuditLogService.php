<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogService
{
    /**
     * Log an append-only audit event.
     */
    public static function log(
        string $action,
        string $subjectType,
        ?string $subjectId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        ?string $correlationId = null
    ): AuditLog {
        $user = Auth::user();

        // Sanitize sensitive fields from payloads
        $cleanBefore = static::sanitize($before);
        $cleanAfter = static::sanitize($after);

        return AuditLog::create([
            'actor_id' => $user?->id,
            'actor_name' => $user->name ?? 'System',
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload_before' => $cleanBefore,
            'payload_after' => $cleanAfter,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'correlation_id' => $correlationId ?? (string) Str::uuid(),
            'created_at' => now(),
        ]);
    }

    /**
     * Remove sensitive keys (e.g. password, tokens) before saving into audit log.
     */
    protected static function sanitize(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $sensitiveKeys = [
            'password',
            'remember_token',
            'token',
            'access_token',
            'refresh_token',
            'id_token',
            'authorization_code',
            'client_secret',
            'secret',
            'api_key',
            'state',
            'nonce',
        ];

        foreach ($payload as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $payload[$key] = '********';
            } elseif (is_array($value)) {
                $payload[$key] = static::sanitize($value);
            }
        }

        return $payload;
    }
}
