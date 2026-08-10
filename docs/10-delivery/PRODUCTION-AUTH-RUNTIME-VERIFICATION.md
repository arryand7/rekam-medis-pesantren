---
id: DOC-PROD-AUTH-RUNTIME-VERIFICATION
title: "Production Authentication Runtime Verification Matrix & Proof of Protection"
status: PASSED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Authentication Runtime Verification Matrix & Proof of Protection

## 1. Verifikasi Runtime Eksternal (Curl Tanpa Cookie)

Hasil eksekusi `curl -skI` terhadap server lokal runtime tanpa session header setelah remediasi diterapkan:

| Target Endpoint | HTTP Status Code | Location Header | Keterangan / Keamanan | Status |
|---|---|---|---|:---:|
| `GET /` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Akses publik dialihkan ke halaman login | ✅ PASSED |
| `GET /dashboard` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Rute dashboard terlindungi middleware auth | ✅ PASSED |
| `GET /patients` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Data pasien terlindungi dari akses anonim | ✅ PASSED |
| `GET /patients/{id}` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Rekam medis pasien terlindungi penuh | ✅ PASSED |
| `GET /visits` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Antrean kunjungan medis terlindungi | ✅ PASSED |
| `GET /pharmacy/inventory` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Inventaris farmasi terlindungi | ✅ PASSED |
| `GET /reports` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Laporan sensus kesehatan terlindungi | ✅ PASSED |
| `GET /people` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Direktori civitas pesantren terlindungi | ✅ PASSED |
| `GET /users` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Akun pengguna terlindungi | ✅ PASSED |
| `GET /roles` | **HTTP 302 Found** | `http://127.0.0.1:8000/login` | Matriks peran & hak akses terlindungi | ✅ PASSED |
| `GET /login` | **HTTP 200 OK** | - | Endpoint publik untuk autentikasi Gate SSO | ✅ PASSED |
| `GET /health` | **HTTP 200 OK** | - | Liveness probe publik (tanpa secret) | ✅ PASSED |
| `GET /health/ready` | **HTTP 200 OK** | - | Readiness probe publik (tanpa secret) | ✅ PASSED |

---

## 2. Matriks Otorisasi & Peran Dashboard Resolver

| Peran Pengguna (*Role*) | Permission Pengenal | Target Rute / View | Perlindungan Privilege |
|---|---|---|---|
| **Tenaga Medis / Dokter / Perawat** | `view-clinical-dashboard` | `/dashboards/clinical` | Akses penuh dashboard klinis & rekam medis |
| **Pembina Asrama / Wali Kelas** | `view-operational-dashboard` | `/dashboards/operational` | Akses disposisi kesehatan tanpa diagnosa medis |
| **Pimpinan Pesantren / Manajemen** | `view-management-dashboard` | `/dashboards/management` | Akses metrik agregat kesehatan |
| **Administrator Teknis** | `manage-users` / `manage-system-settings` | `/` (Admin Shell View) | Akses sistem identitas, tanpa akses rekam medis otomatis |
| **Guest / Unauthenticated** | None | `/login` (HTTP 302 Redirect) | Ditolak dari seluruh data operasional |

---

## 3. Hasil Automated Test Suite

```text
Tests:      197 passed (197 total)
Assertions: 791
Duration:   ~28.5s
Database:   MariaDB 10.4.28 (poskestren_health_test, InnoDB)
Status:     100% Passed (0 Failures, 0 Skipped)
```
