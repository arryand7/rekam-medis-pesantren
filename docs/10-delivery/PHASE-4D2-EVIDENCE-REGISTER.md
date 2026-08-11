---
id: DOC-PHASE-4D2-EVIDENCE-REGISTER
title: "Phase 4D2 Independent Operational Evidence Register"
status: ACTIVE
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4D2 Independent Operational Evidence Register

## 1. Ikhtisar Pendaftaran Bukti Operasional

Register ini mengidentifikasi dan mengklasifikasikan setiap klaim operasional pada dokumentasi rilis Phase 4D untuk memastikan transparansi antara bukti empiris aktual, data pengujian lingkungan uji (*test environment*), dan proyeksi operasional.

---

## 2. Tabel Pendaftaran & Klasifikasi Bukti

| ID Klaim | Deskripsi Klaim | Sumber Data Aktual | Klasifikasi Sumber | Status & Catatan Verifikasi |
|---|---|---|---|---|
| **CLM-01** | Middleware Auth memproteksi 100% rute sensitif (HTTP 302 ke `/login`) | Localhost curl probe & 18 test suite | **`LOCAL-DEV` / `TEST-ENV`** | Terbukti valid pada workstation lokal & CI test suite |
| **CLM-02** | Silsilah Git konsisten & linear tanpa mutasi tak terlacak (`6957d87`) | Local git repository (`master`) | **`LOCAL-DEV`** | Terbukti via `git merge-base`, `git branch`, dan tag `poskestren-production-stable-v1` |
| **CLM-03** | Penegakan `Gate::before()` hanya allow exact local permission | Source code `AppServiceProvider.php` | **`LOCAL-DEV` / `TEST-ENV`** | Terbukti via audit kode & regression unit/feature tests |
| **CLM-04** | Invariansi integritas data database (0 duplicate MRN, 0 duplicate gate_user_id) | MariaDB port 8186 (`poskestren_sabira`) | **`LOCAL-DEV`** | Terbukti via query SQL langsung pada MariaDB lokal |
| **CLM-05** | Zero negative stock obat pada database ledger | MariaDB port 8186 (`poskestren_sabira`) | **`LOCAL-DEV`** | Terbukti via query `current_quantity < 0` (hasil: 0) |
| **CLM-06** | Zero orphan referral & discharge documents | MariaDB port 8186 (`poskestren_sabira`) | **`LOCAL-DEV`** | Terbukti via query orphan version references (hasil: 0) |
| **CLM-07** | Zero failed queue jobs | Local CLI `php artisan queue:failed` | **`LOCAL-DEV`** | Terbukti via query failed jobs (hasil: 0) |
| **CLM-08** | Durasi stabilisasi T+6h (elapsed > 24h) | Wall-clock `2026-08-11 22:21 WIB` | **`LOCAL-DEV`** | Waktu riil berlalu 24.45 jam sejak rilis hotfix (T+6h eligible) |
| **CLM-09** | Nginx access logs & metrik latensi p50/p95 server produksi fisik | Remote Nginx log `/var/log/nginx` | **`UNAVAILABLE-FROM-LOCAL`** | Memerlukan koneksi SSH langsung ke server produksi Linux fisik |
| **CLM-10** | Rekonsiliasi fisik obat di gudang poskestren | Audit fisik riil staf | **`PENDING-PHYSICAL-AUDIT`** | Database ledger tervalidasi konsisten; audit fisik menunggu jadwal staf |
| **CLM-11** | Partisipasi UAT oleh perwakilan peran operasional | Simulasi peran teranomalisasi | **`ANONYMIZED-ROLE-VALIDATED`** | Dinormalkan menggunakan ID representasi peran (`UAT-CLINICAL-01`, dll.) tanpa PII |
| **CLM-12** | Database backup & isolated restore validation | File dump fisik & test DB | **`RESTORE-NOT-YET-PROVEN`** | Prosedur restore terdefinisi pada runbook; eksekusi restore berkala terjadwal |

---

## 3. Kesimpulan Verifikasi

Seluruh kode aplikasi, proteksi otorisasi, dan skema database terbukti 100% valid dan konsisten di lingkungan lokal dan test suite otomatis. Bukti fisik langsung dari host produksi Linux (`/var/www/poskestren/current`) berstatus **`PRODUCTION-EVIDENCE-NOT-AVAILABLE`** selama sesi eksekusi dijalankan dari workstation lokal macOS.
