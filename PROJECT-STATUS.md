---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-15
---

# Status Proyek

## Fase saat ini

**Super Admin SSO Configuration Management** (Status: implementation verified / v0.26.0)

## Super Admin SSO Configuration Management

- [x] Konfigurasi OIDC persisten menggantikan kebutuhan variabel `.env` khusus SSO dengan fallback aman nonaktif/fake.
- [x] Client secret terenkripsi di database, tidak ditampilkan kembali, cache hanya menyimpan ciphertext, dan audit tidak memuat plaintext.
- [x] Exact role `super_admin` mengelola endpoint, Client ID/secret, callback, scopes, app code, aktivasi, timeout/retry/TTL, serta reset melalui UI.
- [x] Login button, redirect, callback, logout, OIDC/sync clients, dan health status memakai read model terpusat serta berlaku segera setelah simpan/reset.
- [x] Aktivasi fail-closed: HTTP mode lengkap, HTTPS (kecuali localhost lokal), callback canonical, `openid`, dan secret wajib.
- [ ] Nilai provider/credential nyata, registered callback, issuer/audience/JWKS, dan login end-to-end tetap harus diverifikasi pada staging.

## Pharmacy Inventory Filter UX

- [x] Pencarian server-side mencakup nama generik, merek, kode obat, nomor batch, serta nama/kode lokasi stok.
- [x] Filter kondisi `tersedia & aman`, `hampir kedaluwarsa`, `kedaluwarsa`, dan `stok habis` memakai semantik kuantitas/tanggal yang sudah menjadi source of truth.
- [x] Filter lokasi, reset, jumlah hasil, empty state terpisah, dan query string persisten pada pagination diterapkan secara responsif dengan semantic theme tokens.
- [x] Permission `view-pharmacy-inventory`, validasi query, targeted regression, full suite, Pint, PHPStan, dan Vite build lulus.
- [ ] In-app browser tidak tersedia; interaksi visual Light/Dark/System pada viewport nyata perlu diperiksa manual.

## Visit Intake Patient Search UX

- [x] Native all-patient dropdown diganti combobox server-side yang mencari nama, nomor RM, dan NIS/NIP.
- [x] Query hanya mengembalikan pasien eligible, maksimal 20 hasil, tanpa data klinis atau kontak sensitif.
- [x] Permission `create-medical-visits`, validasi minimum dua karakter, throttle, keyboard navigation, prefill, empty/error state, dan light/dark semantic styles diterapkan.
- [x] Feature, intake, dan core workflow targeted regression lulus; full quality gate dicatat pada `Last verified`.

## Application Identity & Branding Management

- [x] Persistent single-row identity dengan source fallback, cached read model, dan immediate invalidation.
- [x] Secure PNG/JPEG/WebP branding upload, UUID filename, public/private storage separation, replacement cleanup, reset, dan audit events.
- [x] Original generic Islamic-inspired default mark untuk light/dark/favicon tanpa logo institusi aktual atau protected emblem.
- [x] Admin UI, RBAC `manage-system-settings`, preview, login, header, sidebar, title, footer, dan favicon integration.
- [x] Feature/security/rendering regression tests terarah.
- [x] Full quality, Graphify, dan public-repository hygiene gate lulus.
- [ ] In-app browser tidak tersedia; manual viewport/theme interaction check tetap terbuka tanpa memblokir kontrak render otomatis.

## Public Repository Release Gate

- [x] Current tree dan seluruh 38 commit yang sudah ada pada entry gate diaudit untuk secret, credential, data privat, binary, dump, backup, archive, dan metadata infrastruktur.
- [x] Tidak ditemukan secret aktif atau data pasien nyata; test key historis diklasifikasikan material sintetis.
- [x] `.env.testing` dihapus, safe example/fallback memakai `.test`/`.invalid`, dan fixture realistis dinetralkan.
- [x] `SECURITY.md`, threat model, secret/data scan, entry gate, checklist, final gate, dan regression test ditambahkan.
- [x] `HISTORY_SAFE_TO_PUBLISH=YES`; `SECRET_ROTATION_REQUIRED=NO` berdasarkan evidence repository lokal.
- [ ] Pemilik memilih lisensi dan menetapkan kanal disclosure privat sebelum menyatakan proyek open source.
- [ ] Remote/push/deployment tidak dilakukan; aktivasi kontrol GitHub menjadi tindakan pemilik.

## Light Mode Contrast Hotfix

- [x] Hierarki semantic token light/dark untuk secondary, muted, tertiary, placeholder, disabled, surface subtle dan soft border.
- [x] Shared primitive untuk card, form, banner, badge, filter, table, dan chart labels.
- [x] Halaman prioritas `/referrals`, `/visits/create`, dan `/dashboards/management` serta pola terkait referral diperbaiki tanpa perubahan flow bisnis/backend.
- [x] Kontrak rasio kontras normal text minimum 4.5:1 diuji otomatis.
- [x] Production asset build dan targeted regression suite lulus.
- [ ] Browser visual smoke perlu diulang pada lingkungan yang menyediakan in-app browser; sesi hotfix ini hanya menyediakan inspeksi source/render contract.

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
- [x] **Phase 6A0 — Public Repository Sanitization Release Gate**: Gate teknis selesai (`PUBLIC-GITHUB-READY-WITH-OWNER-LICENSE-DECISION`, v0.24.0).
- [x] **Application Identity & Branding Management**: Selesai dengan manual visual check tersisa (`APPLICATION-IDENTITY-BRANDING-COMPLETE-WITH-MANUAL-VISUAL-CHECK`, v0.25.0).

## Current Environment & Readiness State

```text
Application Development:          ACTIVE
Current Functional Version:       0.26.0 (Super Admin SSO Configuration Management)
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
- Test Suite: 331 tests, 1604 assertions (100% Passed, 0 Skipped, 0 Failed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Passed (0 errors)
- Frontend: Vite Build Passed
- Dependency Install/Audit: `npm ci`, Composer audit, dan npm audit Passed (0 advisory)
- Repository Scan: current tree + 38 pre-existing commits reviewed; no active secret or confirmed real patient data
- Graphify: `graphify update .` Passed (3.723 nodes / 6.027 edges); refresh nama komunitas direkomendasikan setelah perubahan community set.
- git diff --check: PASSED
- Status Rilis: LOCAL PRE-STAGING — Super Admin SSO Configuration Management terverifikasi dengan staging/visual checks tersisa (v0.26.0); no push/deployment
