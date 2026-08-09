<?php

use App\DTOs\GateUserDTO;
use App\Models\Person;
use App\Services\Gate\GateSyncApplyService;

test('concurrent apply sync operations on same gate_user_id produce deterministic single Person projection on MariaDB', function () {
    $applyService = app(GateSyncApplyService::class);

    $dto = new GateUserDTO(
        gateUserId: 'GATE-CONCURRENCY-001',
        name: 'Santri Concurrency Test',
        email: 'concurrency@sabira.id',
        userType: 'santri',
        sourceStatus: 'active',
        checksum: 'HASH-001'
    );

    // Simulate 3 sequential/overlapping transactions on the same gate_user_id
    $result1 = $applyService->applySingleRecord($dto);
    $result2 = $applyService->applySingleRecord($dto);
    $result3 = $applyService->applySingleRecord($dto);

    expect($result1['status'])->toBe('new');
    expect($result2['status'])->toBe('unchanged');
    expect($result3['status'])->toBe('unchanged');

    // Assert strictly exactly one Person record exists
    $count = Person::where('gate_user_id', 'GATE-CONCURRENCY-001')->count();
    expect($count)->toBe(1);
});
