---
id: DOC-PROD-AUTH-HOTFIX-ROLLOUT
title: "Production Authentication Hotfix Rollout Execution & Release Record"
status: APPLIED_AND_VERIFIED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Authentication Hotfix Rollout Execution & Release Record

## 1. Konteks & Ringkasan Rilis Hotfix

- **Hotfix Target**: Penegakan Middleware Autentikasi (`auth`), Pemisahan Otorisasi Route & Resolver Dashboard Berbasis Peran (*Role-Aware Dashboard*)
- **Target Commit Hotfix**: `7be058b` / `58e6205`
- **Tingkat Urgensi**: Critical Hotfix (Security & Privilege Boundary Enforcement)
- **Pelaksana & Penanggung Jawab**: Ryand Arifriantoni
- **Waktu Eksekusi**: 2026-08-10 21:55 WIB
- **Status Akhir**: `AUTH-HOTFIX-PRODUCTION-VERIFIED`

---

## 2. Audit Implementasi `Gate::before()`

Pemeriksaan kode `app/Providers/AppServiceProvider.php`:
```php
Gate::before(function ($user, string $ability): ?bool {
    if ($user && method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
        return true;
    }

    return null;
});
```

### Hasil Audit Integritas:
1. **Penegakan Izin Lokal Eksplisit**: Hanya memberikan hak akses jika `$user->hasPermission($ability)` bernilai `true` (berdasarkan relasi model `roles` $\rightarrow$ `permissions`).
2. **Penyerahan ke Model Policy (*Deferral to Policy*)**: Mengembalikan `null` saat user tidak memiliki permission terkait, sehingga Laravel melanjutkan pengecekan ke Model Policy (`PatientPolicy`, `MedicalVisitPolicy`, dll.) secara proporsional.
3. **Pemisahan Privilege Admin Teknis**: Administrator sistem dengan permission `manage-users` tidak secara otomatis memperoleh akses klinis/rekam medis (`view-patients` / `view-clinical-dashboard`).
4. **Penolakan Ability Asing**: Ability/permission yang tidak terdaftar otomatis menghasilkan evaluasi `false` / 403 Forbidden.
5. **Bebas Rekursi**: Pemanggilan `$user->hasPermission()` murni berbasis Eloquent relasi tanpa memicu `Gate::check()`.

---

## 3. Matriks Otorisasi Rute Aplikasi (*Route Authorization Matrix*)

Seluruh 116 endpoint terdaftar pada `routes/web.php` telah diverifikasi melalui `php artisan route:list -v`:

| Kelompok Rute | Jalur Endpoint | Middleware Stack | Otorisasi Gate / Policy | Akses Guest | Akses Non-Privilege |
|---|---|---|---|:---:|:---:|
| **Public Probes** | `/health`, `/health/ready` | `web` | Public Probe | HTTP 200 | HTTP 200 |
| **Auth Entry** | `/login`, `/auth/gate/*` | `web` | Gate OIDC Flow | HTTP 200 / Redirect | HTTP 200 / Redirect |
| **Root & Resolver** | `/`, `/dashboard` | `web`, `auth` | `DashboardController::index` | HTTP 302 (`/login`) | Role Landing / Safe Shell |
| **Data Pasien** | `/patients`, `/patients/{id}` | `web`, `auth` | `view-patients` (`PatientPolicy`) | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Kunjungan Medis** | `/visits`, `/visits/*` | `web`, `auth` | `MedicalVisitPolicy` / `create-medical-visits` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Pengkajian Klinis**| `/visits/{id}/assessment` | `web`, `auth` | `ClinicalAssessmentPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Observasi** | `/observations`, `/observations/*` | `web`, `auth` | `ObservationEpisodePolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Farmasi & Obat** | `/pharmacy/*` | `web`, `auth` | `MedicinePolicy`, `StockMovementPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Rujukan Eksternal**| `/referrals`, `/referrals/*` | `web`, `auth` | `ReferralPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Kepulangan Pasien**| `/discharges`, `/discharges/*` | `web`, `auth` | `VisitDischargePolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Laporan & Sensus** | `/reports`, `/reports/*` | `web`, `auth` | `ReportPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Integrasi & Outbox**| `/integration/*` | `web`, `auth` | `IntegrationOutboxPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Manajemen User** | `/users`, `/roles`, `/people` | `web`, `auth` | `UserPolicy`, `manage-roles` | HTTP 302 (`/login`) | HTTP 403 Forbidden |
| **Sinkronisasi Gate** | `/gate/*`, `/gate-sync/*` | `web`, `auth` | `GateSyncPolicy`, `GateMappingPolicy` | HTTP 302 (`/login`) | HTTP 403 Forbidden |

---

## 4. Pelaksanaan Atomic Deployment

1. **Pre-Deploy Backup**: Backup basis data dan snapshot release referensi `e3b932d` telah divalidasi.
2. **Build Optimization**:
   - `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`
   - `npm run build` (Vite production bundle tersusun: `app-DeaUjREJ.css`, `app-CmPAdQJl.js`)
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
3. **Atomic Switch & Reload**:
   - Symlink release diarahkan ke build `58e6205`.
   - PHP-FPM dan Queue Workers direstart secara aman (*graceful reload*).
