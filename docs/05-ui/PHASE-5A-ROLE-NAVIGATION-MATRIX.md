---
id: DOC-PHASE-5A-ROLE-NAVIGATION-MATRIX
title: "Phase 5A Role Navigation & UI Permissions Matrix"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-11
---

# Phase 5A Role Navigation & UI Permissions Matrix

Dokumen ini mendefinisikan pemetaan visibilitas menu sidebar/topbar dan wewenang aksi antarmuka pengguna berdasarkan peran (*Role*) di sistem **SABIRA POSKESTREN Health**.

---

## 1. Matriks Visibilitas Menu Navigasi Utama

| Menu Navigasi | Dokter / Nakes (`petugas_kesehatan`) | Apoteker / Farmasi (`petugas_farmasi`) | Pembina Asrama (`pembina_asrama`) | Mudir / Pimpinan (`mudir_pesantren`) | Admin IT (`admin`) |
|---|---|---|---|---|---|
| **Dashboard** | ✅ Klinis (`/dashboards/clinical`) | ✅ Farmasi (`/pharmacy/inventory`) | ✅ Operasional (`/dashboards/operational`) | ✅ Eksekutif (`/dashboards/management`) | ✅ Sistem (`/dashboard`) |
| **Pasien & Rekam Medis** | ✅ Akses Penuh (`/patients`) | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan | 👁️ Direktori Terbatas |
| **Kunjungan Medis** | ✅ Antrean & Input (`/visits`) | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan |
| **Observasi Rawat** | ✅ Bed & Monitoring (`/observations`) | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan |
| **Farmasi & Obat** | 👁️ Tinjau Stok Obat | ✅ Kelola Resep, Stok, Batch (`/pharmacy/*`) | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan |
| **Rujukan Eksternal** | ✅ Buat & Pantau Rujukan (`/referrals`) | ❌ Disembunyikan | 👁️ Logistik & Kepulangan | 👁️ Laporan Agregat | ❌ Disembunyikan |
| **Kepulangan & Handoff** | ✅ Buat Resume Pulang (`/discharges`) | ❌ Disembunyikan | ✅ Konfirmasi Handoff Asrama (`/operational-handoffs`) | ❌ Disembunyikan | ❌ Disembunyikan |
| **Laporan & Statistik** | 👁️ Morbiditas Harian | 👁️ Penggunaan Obat | ❌ Disembunyikan | ✅ Laporan Kesehatan Lengkap (`/reports`) | 👁️ Audit Sistem |
| **Manajemen Sistem** | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan | ❌ Disembunyikan | ✅ User, Role, Gate Sync, Outbox (`/users`, `/gate/*`) |

---

## 2. Prinsip Pemisahan Keamanan Antarmuka (*UI Security Principles*)

1. **Menu Hiding Bukan Otorisasi**:  
   Penyembunyian menu di antarmuka hanyalah peningkatan pengalaman pengguna (*User Experience enhancement*). Setiap akses URL langsung tetap diperiksa secara ketat oleh **Laravel Policy / Gate Server-Side** (mengembalikan HTTP 403 Forbidden bila tidak berhak).
2. **Pemisahan Privasi Data Medis (*Minimum Necessary*)**:  
   Peran non-klinis (seperti Pembina Asrama) hanya menerima data yang telah disanitasi (seperti: status istirahat dan larangan piket/olahraga) tanpa mengekspos diagnosis penyakit, tanda vital, atau daftar obat sensitif.
3. **Pemberitahuan & Alur Interaktif**:  
   Tombol aksi (seperti *Finalisasi Pengkajian*, *Administer Obat*, *Konfirmasi Handoff*) hanya aktif (*enabled*) jika status entitas medis berada dalam siklus hidup (*lifecycle*) yang tepat dan pengguna memiliki izin terkait.
