<?php

use App\Models\Person;
use App\Services\Gate\FakeGateClientService;
use App\Services\Gate\GateSyncDryRunService;

test('dry run sync generates classification report without mutating people database table', function () {
    $service = new GateSyncDryRunService(new FakeGateClientService);

    $initialPersonCount = Person::count();

    $report = $service->execute();

    expect($report['summary']['total'])->toBeGreaterThan(0);
    expect($report['items'])->toBeArray();

    // Verify dry-run DID NOT add or mutate any Person records
    expect(Person::count())->toBe($initialPersonCount);
});

test('dry run sync classifies existing gate_user_id as matched or unchanged', function () {
    // Pre-create person with known gate_user_id
    $person = Person::factory()->create([
        'gate_user_id' => 'GATE-SAN-001',
        'name' => 'Ahmad Santri Syaraf',
        'checksum' => 'hash_san_001',
    ]);

    $service = new GateSyncDryRunService(new FakeGateClientService);
    $report = $service->execute();

    $item = collect($report['items'])->firstWhere('gate_user_id', 'GATE-SAN-001');

    expect($item)->not->toBeNull();
    expect($item['classification'])->toBe('unchanged');
    expect($item['matched_person_id'])->toBe($person->id);
});
