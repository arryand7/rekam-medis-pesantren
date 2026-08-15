<?php

return [
    'cache_key' => 'application_identity.current',
    'disk' => 'public',
    'upload_directory' => 'branding',

    'defaults' => [
        'application_name' => 'SABIRA POSKESTREN Health',
        'application_short_name' => 'POSKESTREN',
        'institution_name' => 'SABIRA',
        'tagline' => 'Layanan Kesehatan Pesantren',
        'description' => 'Aplikasi layanan kesehatan dan rekam medis internal pesantren.',
        'footer_text' => 'SABIRA POSKESTREN Health — Layanan Kesehatan Pesantren',
    ],

    'default_assets' => [
        'logo' => 'branding/default/logo-light.svg',
        'logo_dark' => 'branding/default/logo-dark.svg',
        'favicon' => 'branding/default/favicon.svg',
        'mark' => 'branding/default/app-mark.svg',
    ],
];
