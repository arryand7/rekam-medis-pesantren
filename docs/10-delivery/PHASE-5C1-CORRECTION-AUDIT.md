---
id: DOC-DELIVERY-PHASE5C1-001
title: "Phase 5C1 Correction & Gap Analysis Audit"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-14
---

# Phase 5C1 Correction & Gap Analysis Audit

Dokumen ini melakukan audit dan klasifikasi komponen implementasi Phase 5C sebelum melakukan perbaikan pada Phase 5C1.

## 1. Audit Klasifikasi Komponen

| Komponen | Berkas Terkait | Klasifikasi | Rencana Aksi Phase 5C1 |
|---|---|---|---|
| **Clinical Dashboard & Queues** | `app/Queries/Dashboard/ClinicalDashboardQuery.php`<br>`resources/views/pages/dashboards/clinical.blade.php` | **KEEP** | Pertahankan 6 KPI klinis & 5 work queues, verifikasi mapping status. |
| **Operational Dashboard** | `app/Queries/Dashboard/OperationalDashboardQuery.php`<br>`resources/views/pages/dashboards/operational.blade.php` | **KEEP** | Pertahankan tampilan rest/restrictions privacy-preserving (Minimum Necessary). |
| **Pharmacy Dashboard** | `app/Queries/Dashboard/PharmacyDashboardQuery.php`<br>`resources/views/pages/dashboards/pharmacy.blade.php` | **FIX** | Satukan sumber konfigurasi kedaluwarsa ke `config('pharmacy.expiry_warning_days')` & buat ambang batas low stock fleksibel. |
| **Management Dashboard** | `app/Queries/Dashboard/ManagementDashboardQuery.php`<br>`resources/views/pages/dashboards/management.blade.php` | **FIX** | 1. Optimasi agregasi `computeDailyTrends` (grup SQL tunggal).<br>2. Perbaiki zero denominator follow-up (N/A bukan 100%).<br>3. Gunakan `config('pharmacy.expiry_warning_days')`. |
| **Report Summary KPIs** | `app/Services/Reporting/HealthReportService.php` | **FIX** | `getReportSummary()` wajib menerapkan filter yang persis sama (`start_date`, `end_date`, `status`, `search`) dengan tabel laporan. |
| **Report CSV Export** | `app/Services/Reporting/HealthReportService.php`<br>`app/Http/Controllers/Reporting/HealthReportController.php` | **FIX** | 1. Whitelist tipe laporan ketat (reject unknown).<br>2. Tambah `streamIntegrationDeliveryReport()`.<br>3. Proteksi CSV Formula Injection (`=`, `+`, `-`, `@`). |
| **Date Range Validation** | `app/Http/Controllers/Dashboard/DashboardController.php` | **FIX** | Validasi input tanggal (`date`, `to >= from`, whitelist preset). |
| **Role & Permissions** | `app/Policies/DashboardPolicy.php`<br>`app/Providers/AppServiceProvider.php` | **VERIFY** | Pastikan pengguna role management tidak dapat mengakses report berlevel data pasien individual tanpa izin eksplisit. |
| **Visual UI & Viewports** | Blade Templates | **VERIFY** | Verifikasi render aktual pada 375px, 768px, 1024px, 1440px dan tema Light/Dark/System. |

---

## 2. Definisi Perbaikan Kritis Phase 5C1

1. **Gap A (Report KPI Filter Consistency)**: KPI ringkasan pada view laporan harus 100% mengikuti cakupan filter yang dipilih pengguna, bukan all-time global count.
2. **Gap B (Follow-up Completion Zero Denominator)**: Bila tidak ada data follow-up (`denominator = 0`), KPI harus menampilkan `Belum ada data` / `N/A`, bukan `100%`.
3. **Gap C (Pharmacy Threshold Unification)**: Semua acuan jendela kedaluwarsa merujuk ke `config('pharmacy.expiry_warning_days')`.
4. **Gap D (Export Routing & Integration Support)**: `integration_delivery` memiliki handler ekspor CSV tersendiri, dan tipe laporan yang tidak dikenal menghasilkan response `422 Unprocessable Content`.
5. **Gap E (CSV Formula Injection Protection)**: Seluruh sel teks pada CSV yang diawali karakter formula (`=`, `+`, `-`, `@`, `\t`, `\r`) disanitasi dengan prepending `'`.
6. **Gap F (Management Trend Performance)**: Query tren harian dikonversi dari loop harian menjadi 3 query agregasi `DATE(created_at)` yang konstan.
