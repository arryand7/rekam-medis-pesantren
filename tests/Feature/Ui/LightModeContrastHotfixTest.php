<?php

namespace Tests\Feature\Ui;

function contrastRatio(string $foreground, string $background): float
{
    $luminance = static function (string $hex): float {
        $channels = array_map(
            static fn (string $channel): float => hexdec($channel) / 255,
            str_split(ltrim($hex, '#'), 2),
        );

        $linear = array_map(
            static fn (float $channel): float => $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return (0.2126 * $linear[0]) + (0.7152 * $linear[1]) + (0.0722 * $linear[2]);
    };

    $lighter = max($luminance($foreground), $luminance($background));
    $darker = min($luminance($foreground), $luminance($background));

    return ($lighter + 0.05) / ($darker + 0.05);
}

test('semantic theme text and status pairs meet normal text contrast target', function () {
    $pairs = [
        'light secondary on surface' => ['#334155', '#FFFFFF'],
        'light muted on surface subtle' => ['#475569', '#F8FCFF'],
        'light tertiary and placeholder on surface' => ['#64748B', '#FFFFFF'],
        'light disabled text on disabled surface' => ['#526477', '#E8F3F8'],
        'light info status' => ['#075985', '#E0F2FE'],
        'light success status' => ['#166534', '#DCFCE7'],
        'light warning status' => ['#78350F', '#FFFBEB'],
        'light danger status' => ['#9F1239', '#FFE4E6'],
        'light action text' => ['#FFFFFF', '#0369A1'],
        'light link on surface' => ['#0369A1', '#FFFFFF'],
        'dark muted on surface' => ['#CBD5E1', '#0C2433'],
        'dark tertiary and placeholder on surface' => ['#A9BAC6', '#0C2433'],
        'dark action text' => ['#FFFFFF', '#0C4A6E'],
        'dark link on surface' => ['#7DD3FC', '#0C2433'],
    ];

    foreach ($pairs as $label => [$foreground, $background]) {
        expect(contrastRatio($foreground, $background))
            ->toBeGreaterThanOrEqual(4.5, "$label must meet WCAG AA contrast for normal text");
    }
});

test('prioritized pages use reusable semantic contrast primitives', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    $referralIndex = file_get_contents(resource_path('views/pages/referrals/index.blade.php'));
    $referralCreate = file_get_contents(resource_path('views/pages/referrals/create.blade.php'));
    $visitCreate = file_get_contents(resource_path('views/pages/visits/create.blade.php'));
    $management = file_get_contents(resource_path('views/pages/dashboards/management.blade.php'));

    expect($css)
        ->toContain('--foreground-secondary: #334155')
        ->toContain('--foreground-muted: #475569')
        ->toContain('--placeholder: #64748B')
        ->toContain('--action-bg: #0369A1')
        ->toContain('html.dark')
        ->toContain('.ui-badge-warning')
        ->toContain('.ui-form-control')
        ->toContain('.ui-chart-label');

    expect($referralIndex)
        ->toContain('ui-badge-danger')
        ->toContain('ui-table-heading')
        ->not->toContain('bg-{{')
        ->not->toContain('text-{{');

    expect($referralCreate)
        ->toContain('ui-choice-input')
        ->toContain('ui-form-control')
        ->not->toContain("\$opt['color']");

    expect($visitCreate)
        ->toContain('ui-form-hint')
        ->toContain('ui-banner-warning')
        ->toContain('ui-form-control');

    expect($management)
        ->toContain('ui-filter-chip-active')
        ->toContain('ui-banner-info')
        ->toContain('ui-chart-label')
        ->toContain('ui-card');
});
