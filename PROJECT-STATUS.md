---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-09
---

# Status Proyek

## Fase saat ini

**Phase 3C2 Closed & Validated — Operational Outbox, Role-Aware Dashboards & Health Reports Foundation** (Status: `PRODUCTION-READY-FOUNDATION`)

## Perubahan & Fitur Selesai di Phase 3C2

- [x] **Transactional Integration Outbox (`integration_outbox_events`, `integration_delivery_attempts`)**:
  - Pola transactional outbox asinkron dengan status lifecycle lengkap, concurrency locking (`lockForUpdate()`), idempotensi unik, dan penanganan retry/dead-letter.
- [x] **Kontrak & DTO SABIRA Absensi (`AttendanceIntegrationContract`, `AttendanceHealthDispositionDTO`)**:
  - Standarisasi DTO immutable dengan validasi runtime otomatis terhadap kunci klinis terlarang, driver fake/sandbox untuk pengujian aman, dan kesiapan arsitektural konektor masa depan.
- [x] **Profil Privasi & Isolasi Data Medis (`AttendanceDispositionPayloadBuilder`)**:
  - Penegakan standar *Minimum Necessary*: Nol diagnosis, gejala, resep obat, atau narasi klinis pada payload asrama, guru wali kelas, maupun sistem absensi.
- [x] **Pusat Notifikasi Operasional & Inbox Internal (`operational_notifications`, `user_notifications`)**:
  - Alur notifikasi operasional terarah ke pembina asrama dan wali kelas dengan pelacakan konfirmasi (*acknowledgement*), serta kotak masuk in-app untuk staf poskestren.
- [x] **Dashboard Berbasis Peran (`dashboards.clinical`, `dashboards.management`, `dashboards.operational`)**:
  - Dashboard Klinis untuk dokter/perawat, Dashboard Manajemen Eksekutif (hanya metrik agregat statistik tanpa data individual), dan Dashboard Operasional Asrama & Guru.
- [x] **Pusat Sensus & Laporan Kesehatan (`HealthReportService`)**:
  - Direktori laporan sensus kunjungan, observasi, rujukan eksternal, kepulangan/kontrol, stok obat/kedaluwarsa, dan audit riwayat pengiriman integrasi.
- [x] **Controller, Route & Blade Views Lengkap (0 Route Closures)**:
  - 13 route terdedikasi, 13 permission granular, 5 policy server-side, 4 form request, dan 11 Blade view responsif mendukung light & dark mode.

## Kemajuan Phase

- [x] **Phase 0 — Readiness & Foundation**: Selesai.
- [x] **Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync**: Selesai.
- [x] **Phase 2A — Patient Health Profile & Medical Visit Intake Foundation**: Selesai.
- [x] **Phase 2B — Vital Signs, Clinical Assessment, Initial Actions & Disposition**: Selesai.
- [x] **Phase 2C — POSKESTREN Observation, Periodic Monitoring & Shift Handover**: Selesai.
- [x] **Phase 2D1 — Pharmacy Inventory Foundation & Append-Only Stock Ledger**: Selesai.
- [x] **Phase 2D2 — Medication Orders, Medication Administration, and Atomic Stock Issue**: Selesai.
- [x] **Phase 3A — External Clinical Consultation and Healthcare Partner Integration**: Selesai.
- [x] **Phase 3B — Actual Referral Execution, Transportation, Clinical Handover & Hardening**: Selesai & Tervalidasi di MariaDB.
- [x] **Phase 3C1 — Visit Discharge, Follow-up, Return-to-Activity, and Operational Handoff**: Selesai & Tervalidasi.
- [x] **Phase 3C2 — Operational Outbox, Role-Aware Dashboards & Reporting Foundation**: Selesai & Tervalidasi (Fase 3 Lengkap).
- [ ] **Phase 4 — Telemedicine / Public Portal / Production Hardening**: Menunggu instruksi pengguna.

## Last verified

- Tanggal: 2026-08-09
- Database: MariaDB 10.4.28 (`poskestren_health_test`, InnoDB, REPEATABLE-READ)
- Test Suite: 134 tests, 526 assertions (100% Passed, 0 Skipped, 0 Failed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Frontend: Vite Build Passed (2.52s)
- Route List: 70 routes terdaftar bersih (0 closure pada mutation/action routes)

