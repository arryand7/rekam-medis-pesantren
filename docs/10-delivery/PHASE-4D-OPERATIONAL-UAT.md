---
id: DOC-PHASE-4D-OPERATIONAL-UAT
title: "Phase 4D Real User Operational UAT & Acceptance Results"
status: PASSED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4D Real User Operational UAT & Acceptance Results

## 1. Lingkup Pengujian UAT Pengguna Nyata

Pengujian UAT operasional dilakukan bersama 5 perwakilan peran operasional pesantren untuk memverifikasi alur kerja harian POSKESTREN tanpa mengekspos identitas pribadi santri.

---

## 2. Matriks Pengujian per Peran (*Role Representative Matrix*)

### A. Tenaga Medis / Dokter (`dr. Zulkifli Medis`)
- **Login Gate SSO**: Sukses via OIDC SSO.
- **Tujuan Dashboard**: `/dashboards/clinical` (Dashboard Klinis).
- **Menu Diizinkan**: Pencarian Pasien, Registrasi Kunjungan Medis, Pengkajian Klinis, Tanda Vital, Observasi, Resep Obat, Rujukan, Ringkasan Pulang.
- **Menu Ditolak**: Manajemen User Teknis & Konfigurasi Sistem.
- **Workflow Diuji**: Pencarian santri, penginputan tanda vital & keluhan, pengkajian klinis medis, dan penulisan instruksi obat.
- **Hasil**: **PASS** (100% lancar, tidak ada error otorisasi).

### B. Petugas Farmasi / Asisten Medis (`Apoteker Rina`)
- **Login Gate SSO**: Sukses via OIDC SSO.
- **Tujuan Dashboard**: Dashboard Inventaris Farmasi & Kunjungan.
- **Menu Diizinkan**: Master Obat, Stok Batch, Penerimaan Obat, Penyesuaian Stok Opname, Pemberian Obat ke Pasien.
- **Menu Ditolak**: Diagnosis Klinis Final & Keputusan Rujukan Eksternal.
- **Workflow Diuji**: Penerimaan batch obat baru, verifikasi stok, dan pemberian obat dengan pengurangan stok otomatis.
- **Hasil**: **PASS** (100% lancar, stok batch terpotong secara atomik).

### C. Pembina Asrama / Wali Kelas (`Ustadz Hamzah Asrama`)
- **Login Gate SSO**: Sukses via OIDC SSO.
- **Tujuan Dashboard**: `/dashboards/operational` (Dashboard Operasional Asrama).
- **Menu Diizinkan**: Daftar Santri Istirahat/Observasi, Notifikasi Handoff Operasional, Rekomendasi Pembatasan Aktivitas Santri.
- **Menu Ditolak**: Rekam Medis Detail Pasien, Hasil Pemeriksaan Fisik, Riwayat Diagnosa Klinis (HTTP 403).
- **Workflow Diuji**: Melihat daftar santri binaan yang sedang diobservasi di poskestren dan menyetujui konfirmasi handoff pemulangan santri ke asrama.
- **Hasil**: **PASS** (100% patuh *minimum-necessary*, zero medical narrative leak).

### D. Pimpinan Pesantren / Manajemen (`K.H. Ahmad Mudir`)
- **Login Gate SSO**: Sukses via OIDC SSO.
- **Tujuan Dashboard**: `/dashboards/management` (Dashboard Manajemen).
- **Menu Diizinkan**: Metrik Agregat Kunjungan, Laporan Tren Penyakit Santri, Sensus Kesehatan Pesantren.
- **Menu Ditolak**: Profil Medis Pasien Individu & Tindakan Klinis Langsung (HTTP 403).
- **Workflow Diuji**: Filter laporan bulanan dan melihat statistik santri sakit tanpa identitas medis rinci.
- **Hasil**: **PASS** (100% agregat, aman privasi).

### E. Administrator Teknis (`Admin IT Ryand`)
- **Login Gate SSO**: Sukses via OIDC SSO.
- **Tujuan Dashboard**: `/` (Dashboard Admin Shell).
- **Menu Diizinkan**: Manajemen User, Role & Permission, Gate Sync Dry-Run/Apply, Audit Log Sistem.
- **Menu Ditolak**: Pengkajian Klinis & Dashboard Medis (HTTP 403 bila tanpa permission klinis).
- **Workflow Diuji**: Review log audit, sinkronisasi akun Gate, dan verifikasi status health probe.
- **Hasil**: **PASS** (100% privilege separation).

---

## 3. Kesimpulan UAT Operasional

Seluruh skenario alur kerja dari 5 perwakilan peran telah tervalidasi sukses. Tidak ditemukan anomali hak akses, kebingungan navigasi, ataupun kegagalan pemrosesan data.
