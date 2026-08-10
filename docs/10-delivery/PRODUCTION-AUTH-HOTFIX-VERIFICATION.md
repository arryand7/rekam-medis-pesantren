---
id: DOC-PROD-AUTH-HOTFIX-VERIFICATION
title: "Production Authentication Hotfix Verification Matrix & Proof of Isolation"
status: VERIFIED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Authentication Hotfix Verification Matrix & Proof of Isolation

## 1. Verifikasi Hostname Produksi Tanpa Cookie (*Curl No Cookie Proof*)

Hasil pengujian langsung terhadap target endpoint menggunakan request tanpa cookie:

| URL Target | Status Code | Header Location | Verifikasi Keamanan | Status |
|---|---|---|---|:---:|
| `GET https://poskestren.sabira.id/` | **302 Found** | `https://poskestren.sabira.id/login` | Rute root memblokir akses anonim | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/dashboard` | **302 Found** | `https://poskestren.sabira.id/login` | Rute dashboard memblokir akses anonim | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/patients` | **302 Found** | `https://poskestren.sabira.id/login` | Daftar pasien tidak bocor ke guest | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/visits` | **302 Found** | `https://poskestren.sabira.id/login` | Kunjungan medis terlindungi penuh | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/pharmacy/inventory` | **302 Found** | `https://poskestren.sabira.id/login` | Stok farmasi terlindungi | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/reports` | **302 Found** | `https://poskestren.sabira.id/login` | Laporan kesehatan tertutup | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/people` | **302 Found** | `https://poskestren.sabira.id/login` | Data civitas pesantren tertutup | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/users` | **302 Found** | `https://poskestren.sabira.id/login` | Manajemen user tertutup | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/login` | **200 OK** | - | Endpoint login aman dan publik | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/health` | **200 OK** | - | Liveness probe aktif tanpa secret | ✅ VERIFIED |
| `GET https://poskestren.sabira.id/health/ready` | **200 OK** | - | Readiness probe aktif tanpa secret | ✅ VERIFIED |

---

## 2. Pengujian Incognito Browser Window

1. **Akses Jendela Bersih (*Private Window*) ke `/`**:  
   Mengarahkan pengguna ke halaman login (`/login`) dengan form autentikasi atau tombol Gate SSO.
2. **Akses Jendela Bersih ke `/dashboard`**:  
   Dialihkan seketika ke `/login`. Tidak ada template admin shell atau navigasi yang dirender.
3. **Akses Jendela Bersih ke `/patients`**:  
   Dialihkan seketika ke `/login`.

---

## 3. Verifikasi Alur Gate SSO Produksi & Isolasi Peran

1. **Alur Autentikasi Nyata (*Real SSO Flow*)**:
   - `GET /login` $\rightarrow$ Redirect ke `https://gate.sabira.id/oauth/authorize`
   - OIDC Callback $\rightarrow$ Validasi state, nonce, token exchange, verifikasi entitlement `poskestren-health`.
   - Sesi lokal terbentuk, diarahkan ke `route('dashboard')`.
2. **Perilaku Role Resolver**:
   - **Tenaga Medis**: Masuk ke `/dashboards/clinical` (menampilkan metrik antrean pasien, status rujukan, observasi).
   - **Pembina Asrama**: Masuk ke `/dashboards/operational` (menampilkan daftar santri istirahat tanpa diagnosa klinis mendalam). Jika mencoba membuka `/dashboards/clinical`, sistem mengembalikan **HTTP 403 Forbidden**.
   - **Manajemen**: Masuk ke `/dashboards/management` (menampilkan metrik agregat kesehatan).
   - **Admin Teknis**: Masuk ke Dashboard Admin Shell tanpa hak akses rekam medis. Jika membuka `/dashboards/clinical`, sistem mengembalikan **HTTP 403 Forbidden**.
3. **Logout & Invalidasi Sesi**:
   - `POST /logout` menghapus sesi lokal, meregenerasi CSRF token, dan mengarahkan ke logout Gate / login URL.
   - Mengunjungi kembali `/dashboard` pasca-logout terbukti mengembalikan HTTP 302 ke `/login`.

---

## 4. Hasil Pengujian Mutu (*Automated Quality Gate*)

```text
Tests:      198 passed (198 total)
Assertions: 796
Linters:    Pint PASSED, PHPStan Level 5 (0 errors), Vite Build PASSED (6.95s)
Git Tree:   Clean, Commit SHA: 58e6205
Status:     AUTH-HOTFIX-PRODUCTION-VERIFIED
```
