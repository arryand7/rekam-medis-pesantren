---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Status Proyek

## Fase saat ini

**Phase 4B Closed & Validated — Staging Integration, End-to-End UAT, Gate SSO Activation, Secure Sync Apply, and Attendance Sandbox** (Status: `PRODUCTION-READY-STAGING-VALIDATED`)

## Perubahan & Fitur Selesai di Phase 4B

- [x] **Patient Number Collision Hardening (`Patient::generateUniquePatientNumber`, `Patient::createOrFindForPerson`)**:
  - Eskalasi entropi acak dan penanganan benturan atomik database via retry catch `QueryException` (error 1062 duplicate key). Teruji dengan 1000 pembuatan data sintetis dan konkurensi MariaDB.
- [x] **Attendance Sandbox Integration (`HttpAttendanceSandboxIntegration`)**:
  - Klien HTTP terintegrasi untuk sandbox absensi dengan runtime privacy validator, correlation headers (`X-Poskestren-Event-Id`, `X-Idempotency-Key`), health probe, dan dukungan superseding & revoking disposisi absensi.
- [x] **End-to-End UAT Scenarios (Skenario A–E)**:
  - **Skenario A**: Kunjungan → Pengkajian → Kepulangan Istirahat → Notifikasi Asrama & Wali → Outbox → Sandbox Absensi tuntas tanpa kebocoran data klinis.
  - **Skenario B**: Observasi Poskestren → Pemantauan Selesai Membaik → Kepulangan Kembali Beraktivitas Penuh → Update Absensi.
  - **Skenario C**: Kunjungan Darurat → Rujukan RS Mitra → Pasien Kembali → Review Catatan RS → Kepulangan.
  - **Skenario D**: Amandemen Kepulangan → Event Outbox Pengganti (*superseding outbox event*).
  - **Skenario E**: Deaktivasi Akun di Gate → Akses Ditolak di POSKESTREN → Rekam Medis & Riwayat Kunjungan Pasien Tetap Utuh.
- [x] **Outbox Failure, Retry Backoff & Dead-Letter Recovery**:
  - Penanganan kegagalan pengiriman ke sistem eksternal dengan backoff eksponensial, transisi ke `dead_letter` setelah 5 kali gagal, dan mekanisme retry manual berizin (`POST /integration/outbox/{id}/retry`).
- [x] **Role Matrix & Privacy Isolation**:
  - Pemisahan hak akses menyeluruh antara Administrator Teknis, Pembina Asrama, Wali Kelas, Tenaga Medis, dan Manajemen tanpa kebocoran data diagnosa/klinis.

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
- [x] **Phase 4A — Real Gate SSO, Secure Sync Apply, Application Entitlement & Identity Hardening**: Selesai & Tervalidasi.
- [x] **Phase 4B — Staging Integration, End-to-End UAT, Gate SSO Activation & Attendance Sandbox**: Selesai & Tervalidasi.
- [ ] **Phase 4C — Deployment Hardening & Production Rollout**: Menunggu instruksi pengguna.

## Last verified

- Tanggal: 2026-08-10
- Database: MariaDB 10.4.28 (`poskestren_health_test`, InnoDB, REPEATABLE-READ)
- Test Suite: 173 tests, 659 assertions (100% Passed, 0 Skipped, 0 Failed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Frontend: Vite Build Passed (1.90s)
- Route List: 80+ routes terdaftar bersih (0 closure pada mutation/action routes)
- Production Flags: Semua flag produksi tetap `false`/`fake` (OFF)
