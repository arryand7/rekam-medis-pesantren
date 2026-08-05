<?php

use App\Models\AuditLog;
use App\Services\AuditLogService;

test('audit log service records sanitized append-only event log', function () {
    AuditLogService::log(
        action: 'user.created',
        subjectType: 'User',
        subjectId: '01HJ8ZXYZ...',
        before: null,
        after: ['name' => 'Ahmad', 'password' => 'secret123'],
        reason: 'User registration'
    );

    $log = AuditLog::where('action', 'user.created')->first();

    expect($log)->not->toBeNull();
    expect($log->actor_name)->toBe('System');
    expect($log->payload_after['password'])->toBe('********'); // Sanitized!
});
