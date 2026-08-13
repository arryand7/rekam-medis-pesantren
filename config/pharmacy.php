<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pharmacy Expiry Warning Threshold (Days)
    |--------------------------------------------------------------------------
    |
    | Batas hari peringatan sebelum batch obat dinyatakan hampir kedaluwarsa.
    | Nilai default adalah 30 hari.
    | Status Kebijakan: [PERLU DIKONFIRMASI DENGAN SOP FARMASI RESMI]
    |
    */
    'expiry_warning_days' => (int) env('PHARMACY_EXPIRY_WARNING_DAYS', 30),
];
