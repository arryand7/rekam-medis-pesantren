<?php

use App\DTOs\GateUserDTO;
use App\Models\GateSyncRun;
use App\Models\Person;
use App\Models\User;
use App\Services\Gate\GateSyncApplyService;
use App\Services\Gate\GateSyncDryRunService;

test('dry run sync does not mutate database or create records', function () {
    $dryRunService = app(GateSyncDryRunService::class);

    $initialPersonCount = Person::count();
    $result = $dryRunService->execute(1, 10);

    expect($result['summary']['total'])->toBeGreaterThan(0);
    expect(Person::count())->toBe($initialPersonCount);
});

test('apply sync creates and updates records transactionally and records sync run summary', function () {
    $applyService = app(GateSyncApplyService::class);
    $admin = User::factory()->create();

    $result = $applyService->executeApply(1, 10, $admin);

    expect($result['status'])->toBe('completed');
    expect($result['summary']['applied_new'])->toBeGreaterThan(0);

    $syncRun = GateSyncRun::find($result['run_id']);
    expect($syncRun)->not->toBeNull();
    expect($syncRun->status)->toBe('completed');
    expect($syncRun->executed_by_id)->toBe($admin->id);
});

test('apply sync deactivates user without deleting Person or Patient records', function () {
    $applyService = app(GateSyncApplyService::class);

    $dto = new GateUserDTO(
        gateUserId: 'GATE-DEACT-001',
        name: 'Santri Keluar',
        email: 'keluar@sabira.id',
        userType: 'santri',
        sourceStatus: 'active'
    );

    // Initial apply as active
    $applyService->applySingleRecord($dto);

    $person = Person::where('gate_user_id', 'GATE-DEACT-001')->first();
    expect($person)->not->toBeNull();
    expect($person->user->is_active)->toBeTrue();
    expect($person->patient)->not->toBeNull();

    // Secondary apply as deactivated
    $deactivatedDto = new GateUserDTO(
        gateUserId: 'GATE-DEACT-001',
        name: 'Santri Keluar',
        email: 'keluar@sabira.id',
        userType: 'santri',
        sourceStatus: 'deactivated'
    );

    $applyService->applySingleRecord($deactivatedDto);

    $person->refresh();
    expect($person->source_status)->toBe('deactivated');
    expect($person->user->is_active)->toBeFalse();
    // Patient record must remain intact!
    expect($person->patient)->not->toBeNull();
});
