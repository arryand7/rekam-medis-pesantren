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

    /*
    |--------------------------------------------------------------------------
    | Low Stock Warning Threshold (Units)
    |--------------------------------------------------------------------------
    |
    | Ambang batas jumlah sisa unit obat untuk memicu indikator stok menipis.
    | Jika null / belum dikonfigurasi, sistem menandai indikator sebagai unconfigured.
    | Status Kebijakan: [PERLU DIKONFIRMASI DENGAN SOP FARMASI RESMI]
    |
    */
    'low_stock_threshold' => env('PHARMACY_LOW_STOCK_THRESHOLD') !== null ? (int) env('PHARMACY_LOW_STOCK_THRESHOLD') : null,
];
