---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-09
---

# Status Proyek

## Fase saat ini

**Phase 3C1 Closed & Validated — Visit Discharge, Follow-up, Return-to-Activity, and Operational Handoff** (Status: `PRODUCTION-READY-FOUNDATION`)

## Perubahan & Fitur Selesai di Phase 3C1

- [x] **Agregat Kepulangan & Penutupan Kunjungan (`visit_discharges`, `visit_discharge_versions`)**:
  - Model kepulangan klinis ULID lengkap dengan versioning snapshot immutable, status lifecycle (`draft` -> `finalized` -> `amended` / `entered_in_error`), dan penutupan kunjungan atomik.
- [x] **Discharge Readiness Engine (`EvaluateVisitDischargeReadinessAction`)**:
  - Evaluasi prasyarat teknis/administrasi penutupan kunjungan (pengkajian final, observasi/rujukan tuntas, peringatan obat aktif tanpa auto-discontinue).
- [x] **Rencana Tindak Lanjut / Follow-Up (`visit_follow_up_plans`)**:
  - Perencanaan kontrol ulang, peninjauan obat, evaluasi luka, dan penyelesaian/pembatalan manual berizin dan diaudit.
- [x] **Rekomendasi Aktivitas & Restriksi (`activity_restrictions`)**:
  - Penerbitan surat/rekomendasi istirahat, bed rest, dan pembatasan aktivitas fisik berjangka waktu.
- [x] **Serah Terima Operasional Internal (`clinical_operational_handoffs`)**:
  - Handoff instruksi perawatan ke pengasuh asrama/guru berprinsip *minimum-necessary privacy* dengan pelacakan konfirmasi penerimaan (*acknowledgement*).
- [x] **Dokumen Ringkasan Kepulangan Privat (`discharge_documents`)**:
  - Penyimpanan berkas privat di `storage/app/private/discharges` dengan SHA-256 integrity hash, nama file ULID opaque, dan unduhan berizin serta diaudit.
- [x] **Controller & Otorisasi Lengkap**:
  - 11 route kepulangan ditangani 5 controller terdedikasi di `App\Http\Controllers\Discharge\*` (0 Route Closures).
  - 9 granular permission dan Policy server-side.

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
- [ ] **Phase 3C2 / Phase 4 — Final Discharge Hardening / Auth / Telemedicine**: Menunggu instruksi pengguna.


## Last verified

- Tanggal: 2026-08-09
- Database: MariaDB 10.4.28 (`poskestren_health_test`, InnoDB, REPEATABLE-READ)
- Test Suite: 85 tests, 258 assertions (100% Passed, 0 Skipped, 0 Failed)
- Concurrency Group: 4 passed on MariaDB
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Frontend: Vite Build Passed (2.58s)
- Route List: 57 routes terdaftar bersih (0 closure pada mutation/referral routes)
