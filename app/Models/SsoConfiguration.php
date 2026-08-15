<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SsoConfiguration extends Model
{
    use HasUlids;

    protected $fillable = [
        'singleton',
        'sso_enabled',
        'driver',
        'base_url',
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'app_code',
        'http_timeout',
        'retry_attempts',
        'retry_backoff_ms',
        'entitlement_ttl_seconds',
    ];

    protected $hidden = [
        'client_secret',
    ];

    protected function casts(): array
    {
        return [
            'singleton' => 'boolean',
            'sso_enabled' => 'boolean',
            'client_secret' => 'encrypted',
            'http_timeout' => 'integer',
            'retry_attempts' => 'integer',
            'retry_backoff_ms' => 'integer',
            'entitlement_ttl_seconds' => 'integer',
        ];
    }
}
