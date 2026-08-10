---
id: DOC-PHASE-4D2-UAT-SIGNOFF
title: "Phase 4D2 Anonymized Role-Based Operational UAT Sign-Off Record"
status: VALIDATED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4D2 Anonymized Role-Based Operational UAT Sign-Off Record

## 1. Standar Dokumentasi UAT Tanpa PII

Dokumentasi UAT operasional menggunakan identifikasi peran teranomalisasi (*Anonymized Role Identifiers*) untuk melindungi privasi staf dan praktisi pesantren.

---

## 2. Catatan Pengujian per Peran

### 1. `UAT-CLINICAL-01` — Tenaga Medis / Dokter
- **Hak Akses Peran**: `petugas_kesehatan` (`view-clinical-dashboard`, `view-patients`, `view-medical-visits`, `create-medical-visits`, `create-clinical-assessments`, `record-vital-signs`, `create-medication-orders`, `create-referrals`, `create-visit-discharges`)
- **Login Gate SSO**: **PASS** (Tervalidasi via OIDC Authorization Code Flow)
- **Tujuan Dashboard**: `/dashboards/clinical` (HTTP 200)
- **Aksi Diizinkan**: Pencarian Pasien, Pendaftaran Kunjungan, Pemeriksaan Fisik & Tanda Vital, Resep Obat, Rujukan, Ringkasan Pulang (Semua **PASS**)
- **Aksi Ditolak**: Manajemen User Teknis & Konfigurasi Sistem (HTTP 403 - **PASS**)
- **Logout & Sesi**: Sesi terhapus sempurna, direct access kembali mengembalikan HTTP 302 ke `/login` (**PASS**)
- **Status Sign-Off**: **SIGNED-OFF**

### 2. `UAT-PHARMACY-01` — Petugas Farmasi / Asisten
- **Hak Akses Peran**: `petugas_farmasi` (`view-pharmacy-inventory`, `manage-medicine-master`, `receive-medicine-stock`, `adjust-medicine-stock`, `administer-medications`)
- **Login Gate SSO**: **PASS**
- **Tujuan Dashboard**: `/pharmacy/inventory` (HTTP 200)
- **Aksi Diizinkan**: Input Batch Obat Baru, Stok Opname, Pemotongan Stok Atomik saat Pemberian Obat (**PASS**)
- **Aksi Ditolak**: Pengesahan Diagnosis Medis & Ringkasan Pulang (HTTP 403 - **PASS**)
- **Logout & Sesi**: **PASS**
- **Status Sign-Off**: **SIGNED-OFF**

### 3. `UAT-DORM-01` — Pembina Asrama / Wali Asrama
- **Hak Akses Peran**: `pembina_asrama` (`view-operational-dashboard`, `view-operational-notifications`, `acknowledge-clinical-operational-handoffs`)
- **Login Gate SSO**: **PASS**
- **Tujuan Dashboard**: `/dashboards/operational` (HTTP 200)
- **Aksi Diizinkan**: Melihat Daftar Santri Istirahat/Observasi, Menerima Handoff Pemulangan Santri ke Asrama (**PASS**)
- **Aksi Ditolak**: Akses Rekam Medis Pasien, Diagnosa Klinis Detail, Riwayat Obat Spesifik (HTTP 403 - **PASS**)
- **Logout & Sesi**: **PASS**
- **Status Sign-Off**: **SIGNED-OFF**

### 4. `UAT-MANAGEMENT-01` — Pimpinan Pesantren / Manajemen
- **Hak Akses Peran**: `pimpinan` (`view-management-dashboard`, `view-health-reports`, `export-health-reports`)
- **Login Gate SSO**: **PASS**
- **Tujuan Dashboard**: `/dashboards/management` (HTTP 200)
- **Aksi Diizinkan**: Metrik Agregat Kesehatan Pesantren, Laporan Tren Sakit Santri (**PASS**)
- **Aksi Ditolak**: Profil Rekam Medis Santri Individu & Penginputan Tindakan Klinis (HTTP 403 - **PASS**)
- **Logout & Sesi**: **PASS**
- **Status Sign-Off**: **SIGNED-OFF**

### 5. `UAT-IT-01` — Administrator Teknis / IT
- **Hak Akses Peran**: `admin` (`manage-users`, `manage-roles`, `view-audit-log`, `view-gate-sync`, `execute-gate-sync-apply`)
- **Login Gate SSO**: **PASS**
- **Tujuan Dashboard**: `/` (Dashboard Admin Shell - HTTP 200)
- **Aksi Diizinkan**: Manajemen User, Role & Permissions, Sinkronisasi Identitas Gate, Log Audit (**PASS**)
- **Aksi Ditolak**: Akses Dashboard Klinis Medis tanpa Permission Klinis (HTTP 403 - **PASS**)
- **Logout & Sesi**: **PASS**
- **Status Sign-Off**: **SIGNED-OFF**

---

## 3. Ringkasan Penerimaan

Semua 5 peran representatif operasional terbukti terisolasi sesuai prinsip *Least Privilege* dan *Minimum Necessary*.
