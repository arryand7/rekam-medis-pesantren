<?php

function publicRepositoryRoot(): string
{
    return dirname(__DIR__, 2);
}

it('keeps public configuration examples free from populated secrets', function () {
    $environment = file_get_contents(publicRepositoryRoot().'/.env.example');

    expect($environment)
        ->not->toBeFalse()
        ->toContain('ATTENDANCE_INTEGRATION_ENDPOINT_URL=https://attendance.example.invalid')
        ->toContain("APP_KEY=\n")
        ->toContain("ATTENDANCE_INTEGRATION_API_KEY=\n")
        ->not->toContain('GATE_CLIENT_SECRET=')
        ->not->toContain('GATE_BASE_URL=')
        ->not->toContain('/Users/')
        ->not->toContain('/Applications/');
});

it('ignores secrets private data exports and transient ai prompts', function () {
    $gitignore = file_get_contents(publicRepositoryRoot().'/.gitignore');

    expect($gitignore)
        ->not->toBeFalse()
        ->toContain('/.env.*')
        ->toContain('!/.env.example')
        ->toContain('*.pem')
        ->toContain('*.p12')
        ->toContain('*.sql')
        ->toContain('/storage/app/private/**')
        ->toContain('/CODEX-PROMPT-*.md');

    $rootPrompts = array_merge(
        glob(publicRepositoryRoot().'/*PROMPT*.md') ?: [],
        glob(publicRepositoryRoot().'/PROMPT-*.md') ?: [],
    );

    expect(array_values(array_unique($rootPrompts)))->toBe([]);
});

it('uses explicitly synthetic defaults and publishes security guidance', function () {
    $phpunit = file_get_contents(publicRepositoryRoot().'/phpunit.xml');
    $gate = file_get_contents(publicRepositoryRoot().'/config/gate.php');
    $composer = json_decode((string) file_get_contents(publicRepositoryRoot().'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($phpunit)
        ->not->toBeFalse()
        ->toContain('base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=')
        ->not->toContain('/Users/')
        ->not->toContain('/Applications/');
    expect($gate)->not->toBeFalse()->toContain('https://gate.example.invalid');
    expect($composer['license'])->toBe('proprietary');
    expect(publicRepositoryRoot().'/SECURITY.md')->toBeFile();
    expect(publicRepositoryRoot().'/.env.testing')->not->toBeFile();
});

it('contains the public repository release evidence set', function () {
    $requiredDocuments = [
        'docs/07-security/PUBLIC-REPOSITORY-THREAT-MODEL.md',
        'docs/10-delivery/PUBLIC-REPOSITORY-ENTRY-GATE.md',
        'docs/10-delivery/PUBLIC-REPOSITORY-SANITIZATION-AUDIT.md',
        'docs/10-delivery/GITHUB-PUBLIC-REPOSITORY-CHECKLIST.md',
        'docs/10-delivery/PUBLIC-GITHUB-RELEASE-GATE.md',
        'docs/10-delivery/PUBLIC-REPOSITORY-SECRET-SCAN.md',
        'docs/10-delivery/PUBLIC-REPOSITORY-DATA-SANITIZATION.md',
    ];

    foreach ($requiredDocuments as $document) {
        expect(publicRepositoryRoot().'/'.$document)->toBeFile();
    }
});
