---
id: DOC-PHASE-4B-GATE-SSO-UAT
title: "Phase 4B Gate SSO & Entitlement UAT Report"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4B Gate SSO & Entitlement UAT Report

## 1. Ikhtisar Pengujian

Pengujian ini memverifikasi integrasi nyata antara SABIRA POSKESTREN Health dan Gate IdP pada lingkungan staging, mencakup alur OAuth2 Authorization Code, validasi state/nonce, penegakan hak akses aplikasi (application entitlement), dan pemisahan wewenang klinis.

## 2. Matriks Pengujian Hak Akses Aplikasi (Application Entitlement)

| Kategori Akun Gate | Status Akun Gate | Entitlement POSKESTREN | Perilaku yang Diharapkan | Hasil Pengujian | Status |
|---|---|---|---|---|:---:|
| Tenaga Kesehatan (Dokter/Perawat) | Active | `allowed` | Login sukses → Proyeksi Person & User → Dashboard Klinis | Login berhasil, hak akses klinis aktif sesuai role lokal | ✅ PASS |
| Staf Pengajar / Wali Kelas | Active | `allowed` | Login sukses → Proyeksi Person & User → Dashboard Operasional | Login berhasil, akses dibatasi ke ringkasan operasional | ✅ PASS |
| Santri (Pasien) | Active | `allowed` | Login sukses → Akses portal pasien (bukan dashboard klinis) | Login berhasil, tidak dapat mengakses modul klinis | ✅ PASS |
| Pengguna Tanpa Izin Aplikasi | Active | `not_assigned` | 302 Redirect ke `/auth/gate/access-denied` | Akses ditolak, sesi lokal tidak dibuat, audit log tercatat | ✅ PASS |
| Pengguna Izin Dicabut | Active | `revoked` | 302 Redirect ke `/auth/gate/access-denied` | Akses ditolak, audit log tercatat | ✅ PASS |
| Pengguna Dinonaktifkan di Gate | `suspended`/`deactivated` | `allowed` | Middleware menolak akses, force logout | Force logout ke `/login`, rekam medis pasien tetap utuh | ✅ PASS |
| Akun Layanan Teknis (Bot/Service) | Active | `allowed` | Akses API/Sync sesuai permission, tidak dibuatkan entitas pasien | Berfungsi, person flagged non-human (`is_eligible = false`) | ✅ PASS |

## 3. Verifikasi Keamanan Protokol OIDC Staging

- **State Parameter Validation**: Menggunakan `hash_equals()` dan penghapusan token satu-kali pakai (`session()->pull()`) mencegah serangan CSRF & authorization code injection.
- **Replay Protection**: Nonce diteruskan saat authorization request.
- **Entitlement Separation**: Gate entitlement `allowed` hanya membuka pintu masuk aplikasi. Hak akses ke rekam medis sepenuhnya diatur oleh Laravel Policy & Gate server-side.
- **Gate Admin Guard**: Akun dengan claim Gate `admin`/`school_admin` tidak secara otomatis memperoleh akses rekam medis (`view-clinical-dashboard`, `view-medical-record`).
- **Non-Destructive Deactivation**: Pencabutan hak akses di Gate atau penonaktifan akun tidak pernah menghapus record `Person`, `Patient`, maupun riwayat kunjungan medis.
