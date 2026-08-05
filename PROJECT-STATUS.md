---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Status Proyek

## Fase saat ini

**Phase 1 Completed — Identity, Access Control, Gate Contract & Dry-Run Sync**

## Perubahan & Fitur Selesai di Phase 1

- [x] **Phase 0 Closure**: Dikonfirmasi `PASSED` (`docs/10-delivery/PHASE-0-CLOSURE.md`).
- [x] **Pemisahan Identitas (Person, User, Patient)**:
  - Skema ULID untuk `people`, `users`, dan `patients`.
  - Aturan: `Person` manusia (santri, guru, staf, pengasuh, admin) eligible sebagai `Patient`. Akun teknis/bot tidak eligible.
  - Deaktivasi `User` tidak menghapus record `Person` atau `Patient`.
- [x] **Role, Permission & Authorization Server-Side**:
  - Skema `roles`, `permissions`, `model_has_roles`, `role_has_permissions`.
  - Hak akses berbasis Policy (`UserPolicy`, `PersonPolicy`, `PatientPolicy`, `GateSyncPolicy`, `AuditLogPolicy`).
  - Aturan keamanan: Admin tidak otomatis memiliki akses data medis tanpa permission `view-patients`.
- [x] **Audit Log Append-Only**:
  - Skema `audit_logs` append-only dengan penguraian data sensitif (sanitizing password/token).
  - Integrasi via `AuditLogService`.
- [x] **Gate SSO Client Contract**:
  - Implementation `GateClientContract`, `GateUserDTO`, dan `FakeGateClientService`.
- [x] **Gate Dry-Run Sync Engine**:
  - `GateSyncDryRunService` dengan klasifikasi 10 status (`new`, `matched`, `changed`, `deactivated`, `conflict`, dll.).
  - Diisolasi secara **Non-Mutating** (tidak merubah data database utama saat dry-run).
  - Aturan pencocokan: `gate_user_id` -> candidate review -> new. Dilarang auto-merge berdasarkan nama.
- [x] **Halaman Management UI Shell**:
  - People directory, Patient eligibility status, Users management, Roles & Permissions, Gate Sync Preview, Identity Conflicts, dan Audit Log viewer.

## Kemajuan Phase

- [x] **Phase 0 — Readiness & Foundation**: Selesai.
- [x] **Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync**: Selesai.
- [ ] **Phase 2 — Modul Pelayanan Medis & Rekam Kesehatan**: Menunggu persetujuan pengguna.

## Last verified

- Tanggal: 2026-08-05
- Test Suite: 12 tests, 44 assertions (100% Passed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
