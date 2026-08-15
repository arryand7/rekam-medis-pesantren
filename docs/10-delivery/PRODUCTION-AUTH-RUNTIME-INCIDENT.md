---
id: DOC-PROD-AUTH-RUNTIME-INCIDENT
title: "Production Authentication Runtime Incident Report & Root Cause Analysis"
status: RESOLVED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Authentication Runtime Incident Report & Root Cause Analysis

## 1. Ringkasan Eksekutif & Klasifikasi Insiden

- **Nama Insiden**: Unauthenticated Root & Early Phase Route Access Discrepancy
- **Klasifikasi Awal**: `PRODUCTION-AUTH-INCIDENT-UNDER-REVIEW`
- **Klasifikasi Akhir**: `AUTH-BYPASS-FIXED` (Telah dimitigasi, dilindungi auth middleware, tervalidasi via curl tanpa cookie, dan dilindungi 15 automated security regression tests)
- **Tingkat Keparahan**: Critical (Privilege boundary discrepancy)
- **Waktu Temuan**: 2026-08-10 21:09 WIB
- **Waktu Resolusi**: 2026-08-10 21:35 WIB

---

## 2. Temuan Investigasi & Bukti Runtime (*Evidence-Based Root Cause*)

### A. Pengujian Awal via Curl (Tanpa Cookie)
Sebelum perbaikan dilakukan, uji curl tanpa header cookie menunjukkan perilaku berikut:
- `GET /` -> Mengembalikan **HTTP 200 OK** (menampilkan view `dashboard.blade.php` langsung kepada guest).
- `GET /patients`, `GET /visits`, `GET /people`, `GET /users`, `GET /roles` -> Mengembalikan **HTTP 200 OK** kepada guest.
- `GET /referrals`, `GET /discharges`, `GET /reports`, `GET /gate/*` -> Mengembalikan **HTTP 302 Redirect ke /login** (sudah terlindungi auth sejak Phase 3B/4A).

### B. Akar Masalah (*Root Cause*)
1. **Rute Phase 0 & Phase 1 di Luar Group Middleware `auth`**:
   - Pada file `routes/web.php`, route definition untuk root `/` (`Route::get('/', fn () => view('dashboard'))`) dan rute modul Phase 1–2A (`/people`, `/patients`, `/visits`, `/observations`, `/pharmacy/*`, `/consultations`) didefinisikan sebelum blok `Route::middleware('auth')->group(...)`.
2. **Ketiadaan Resolver Dashboard Berbasis Peran**:
   - Rute root `/` mengarah langsung ke view static admin shell (`dashboard.blade.php`) daripada memanggil controller resolver untuk mengecek otentikasi dan mengarahkan pengguna ke dashboard yang sesuai dengan hak akses perannya (*Clinical, Operational, Management, atau Admin*).
3. **Session Browser Pengguna**:
   - Sesi pengguna pada browser normal yang sebelumnya telah diautentikasi memperkuat persepsi bahwa pengguna langsung masuk ke dashboard tanpa proses login.

---

## 3. Tindakan Remediasi yang Telah Diterapkan

1. **Pengelompokan Rute Seluruh Aplikasi ke Middleware `auth`**:
   - Seluruh rute aplikasi (kecuali endpoint publik `/health`, `/health/ready`, `/login`, `/auth/gate/callback`, `/auth/gate/access-denied`, dan `/logout`) dipindahkan ke dalam group `Route::middleware('auth')->group(...)`.
2. **Implementasi Gate Authorization di Tingkat Route Callback**:
   - Rute closure (seperti `/people`, `/patients`, `/users`, `/visits`, `/observations`, `/pharmacy/*`, `/consultations`) ditambahkan otorisasi eksplisit `Gate::authorize('<permission-name>')`.
3. **Penyempurnaan `DashboardController::index()`**:
   - Rute root `/` dan `/dashboard` dialihkan ke `DashboardController::index()`.
   - Mengalihkan pengguna non-autentikasi ke `/login`.
   - Mengalihkan pengguna medis ke `dashboards.clinical`.
   - Mengalihkan staf asrama ke `dashboards.operational`.
   - Mengalihkan pimpinan ke `dashboards.management`.
   - Mengalihkan admin teknis ke `dashboard` admin tanpa hak akses klinis otomatis.
4. **Penambahan Automated Regression Test Suite**:
   - Dibuat test suite baru [`AuthenticationRuntimeAuditAndProtectionTest.php`](../../tests/Feature/Auth/AuthenticationRuntimeAuditAndProtectionTest.php) (15 tests baru, 72 assertions) yang memverifikasi guest redirection, role-aware routing, privilege isolation, entitlement rejection, dan invalidasi sesi saat logout.
