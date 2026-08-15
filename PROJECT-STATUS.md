---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-15
---

# Status Proyek

## Fase saat ini

**Phase 5D Complete — Pre-Staging Acceptance & Deployment Readiness** (Status: `PHASE-5D-PRE-STAGING-READY-WITH-MANUAL-ITEMS` / v0.23.0)

## Perubahan & Temuan di Phase 5D

- [x] Entry/final quality gate, dependency advisory audit, cache rehearsal dan migration rehearsal pada database terisolasi.
- [x] RBAC administration Phase 5D0 dengan protected core roles, direct permission exception, anti-escalation dan audit trail.
- [x] Seeder aman untuk staging: data demo opt-in dan baseline permission non-destruktif.
- [x] Outbox command + scheduler, log/privacy redaction, private-by-default storage dan trusted proxy configuration.
- [x] Backup/restore lokal terisolasi dan first-staging deployment/rollback/security runbooks.
- [ ] Browser smoke, nilai Gate/Attendance nyata, TLS/proxy CIDR dan server topology harus diverifikasi pada staging pertama.

## Perubahan & Temuan di Phase 5C2

- [x] **Eliminasi Double-Counting Kedaluwarsa Farmasi**: Kategori batch obat `expired` (`expiry_date < today AND qty > 0`), `near_expiry` (`today <= expiry_date <= threshold AND qty > 0`), `normal` (`expiry_date > threshold AND qty > 0`), dan `depleted` (`qty <= 0`) dijamin 100% saling lepas (*mutually exclusive*).
- [x] **Shared Model Scopes**: Penambahan scope `expired()`, `nearExpiry()`, `normal()`, dan `depleted()` pada `MedicineBatch` sebagai sumber kebenaran tunggal (*Single Source of Truth*).
- [x] **Dashboard Manajemen Batch Health**: 4 kategori status batch pada Dashboard Manajemen konsisten secara matematis (jumlah keempat bucket tepat sama dengan total batch aktif).
- [x] **Semantik Snapshot Laporan Stok Farmasi**: Laporan `pharmacy_stock` secara eksplisit ditetapkan sebagai *Current Inventory Snapshot*. Input tanggal (`start_date`/`end_date`) disembunyikan dan digantikan dengan input pencarian kata kunci obat/batch.
- [x] **Metadata Ekspor CSV Snapshot**: Metadata CSV menyajikan judul `"Snapshot Stok Farmasi Saat Ini"` dan tidak lagi memuat baris rentang tanggal semu.
- [x] **Status Kolom Akurat**: Kolom status pada tabel dan file ekspor CSV menyajikan status riil (`Kedaluwarsa`, `Hampir Kedaluwarsa`, `Aktif`, `Habis`).
- [x] **Automated Regression Suite**: Penambahan 4 test case komprehensif pada `tests/Feature/Ui/Phase5C2PharmacyReportingClosureTest.php`, total test suite meningkat menjadi **244 tests / 1043 assertions (100% PASS)**.

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
- [x] **Phase 3C2 — Operational Outbox, Role-Aware Dashboards & Reporting Foundation**: Selesai & Tervalidasi.
- [x] **Phase 4A — Real Gate SSO, Secure Sync Apply, Application Entitlement & Identity Hardening**: Selesai & Tervalidasi.
- [x] **Phase 4B — Staging Integration, End-to-End UAT, Gate SSO Activation & Attendance Sandbox**: Selesai & Tervalidasi.
- [x] **Phase 4C — Deployment Hardening, Controlled Cutover, Rollback & Go-Live Validation**: Selesai (Rehearsal Pre-Production Validated).
- [x] **Phase 4C2 — Controlled Cutover Rehearsal & Canary Simulation**: Selesai (`PRE-PRODUCTION-CUTOVER-REHEARSAL-PASSED`).
- [x] **Phase 4D — Post-Go-Live Runbooks, Operational Acceptance & Baseline**: Selesai (`PRE-PRODUCTION-OPERATIONAL-READINESS-VALIDATED`).
- [x] **Phase 5A — Documentation Truth Normalization & Workflow Audit**: Selesai (`DOCS-AUDIT-COMPLETE`).
- [x] **Phase 5A1 — Evidence-Backed UX & Core Workflow Code Completion**: Selesai (`PHASE-5A1-COMPLETE`, v0.19.3 Baseline).
- [x] **Phase 5A2 — Visual Browser Verification, Diff Hygiene & Final Acceptance**: Selesai (`PHASE-5A-FINAL-ACCEPTED`).
- [x] **Phase 5B — Clinical Workflow Continuity & Clinical Workspace Polish**: Selesai (`PHASE-5B-ACCEPTED`, v0.20.0 Baseline).
- [x] **Phase 5B1 — Final Verification, Test Portability, Browser Acceptance & Repository Hygiene**: Selesai (`PHASE-5B-COMPLETE`, v0.20.1).
- [x] **Phase 5B2 — Repository Hygiene Finalization, Bug Fix & Final Closure**: Selesai (`PHASE-5B-FINAL-COMPLETE`, v0.20.2).
- [x] **Phase 5C — Role-Aware Dashboards, Actionable Work Queues, Operational Reports & Streaming Export**: Selesai (`PHASE-5C-FINAL-COMPLETE`, v0.21.0).
- [x] **Phase 5C1 — Reporting Correctness, Privacy Boundaries, Query Performance & Visual Closure**: Selesai (`PHASE-5C-FINAL-COMPLETE`, v0.21.1).
- [x] **Phase 5C2 — Pharmacy Reporting Semantics & Final Micro-Closure**: Selesai (`PHASE-5C-FINAL-COMPLETE`, v0.21.2).
- [x] **Phase 5D0 — RBAC Permission Administration Hardening**: Selesai (`PHASE-5D0-RBAC-COMPLETE`, digabung dalam v0.23.0).
- [x] **Phase 5D — Pre-Staging Acceptance & Deployment Readiness**: Selesai bersyarat (`PHASE-5D-PRE-STAGING-READY-WITH-MANUAL-ITEMS`, v0.23.0).

## Current Environment & Readiness State

```text
Application Development:          ACTIVE
Current Functional Version:       0.23.0 (Phase 5D Pre-Staging Acceptance Complete with Manual Staging Items)
Environment:                      LOCAL-DEVELOPMENT (macOS Developer Workstation)
Deployment Status:                NOT_DEPLOYED (Belum pernah dideploy ke server fisik)
Production Host Status:           NOT_STARTED
Production Server Validation:     NOT_APPLICABLE_YET
Staging Deployment:               PENDING
Gate Real Environment Validation: PENDING
Attendance Sandbox Validation:    LOCAL_SIMULATION_VALIDATED
```

## Last verified

- Tanggal: 2026-08-15
- Database: MariaDB 10.4.28 (`poskestren_sabira`, InnoDB, REPEATABLE-READ)
- Test Suite: 277 tests, 1218 assertions (100% Passed, 0 Skipped, 0 Failed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Passed (0 errors)
- Frontend: Vite Build Passed
- git diff --check: PASSED
- Status Rilis: LOCAL PRE-STAGING — PHASE-5D-PRE-STAGING-READY-WITH-MANUAL-ITEMS (v0.23.0); no staging/production deployment
