---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-09
---

# Status Proyek

## Fase saat ini

**Phase 3B Closed & Validated — Actual Referral, Transport, Clinical Handover, Return, and MariaDB Concurrency Hardening** (Status: `PRODUCTION-READY-FOUNDATION`)

## Perubahan & Fitur Selesai di Phase 3B

- [x] **Agregat Rujukan Eksternal (`referrals`)**:
  - Model rujukan ULID lengkap dengan status machine (`prepared` -> `approved` -> `ready_to_depart` -> `departed` -> `arrived` -> `accepted` -> `under_external_care` -> `return_planned` -> `returned` -> `completed`).
- [x] **Snapshot Dokumen & Private Storage (`referral_versions`, `referral_documents`)**:
  - Snapshot rekam medis immutable dengan SHA-256 checksum.
  - Berkas private tersimpan di disk `referral_documents` (`storage/app/private/referrals`) dengan nama file ULID opaque tanpa data identitas pasien.
  - Proteksi path traversal dan audit log pada setiap unduhan berkas.
- [x] **Logistik Transportasi & Pendamping (`referral_transports`, `referral_companions`)**:
  - Manajemen moda transportasi (ambulans mitra/sekolah/kendaraan pribadi) dan pendampingan santri.
- [x] **Serah Terima Klinis & Status Destinasi (`referral_handovers`, `referral_status_events`)**:
  - Serah terima klinis dengan idempotency key.
  - Pelacakan status fasilitas penerima secara granular (handoff ≠ acceptance).
- [x] **Kepulangan & Tinjauan Klinis Lokal (`referral_returns`, `referral_return_reviews`)**:
  - Pencatatan kepulangan dengan one-return guard dan timestamp server.
  - Tinjauan klinis kepulangan lokal poskestren dengan rekonsiliasi obat (tanpa auto-discharge).
- [x] **Controller & Policy Refactoring**:
  - 13 Route rujukan direfaktor ke 9 Controller method dedicated di `App\Http\Controllers\Referral` (0 Route Closures).
  - Otorisasi server-side `$this->authorize()` (Policy-enforced) di setiap endpoint.
  - Form Requests untuk seluruh input mutasi data rujukan.
- [x] **MariaDB Concurrency Tests**:
  - 4 concurrency test invariant dibuktikan pada MariaDB 10.4.28 nyata (one-active-referral lock, referral number uniqueness, handoff idempotency, one-return guard).

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
- [ ] **Phase 3C / Phase 4 — Final Discharge / Auth / Telemedicine**: Menunggu instruksi pengguna.

## Last verified

- Tanggal: 2026-08-09
- Database: MariaDB 10.4.28 (`poskestren_health_test`, InnoDB, REPEATABLE-READ)
- Test Suite: 85 tests, 258 assertions (100% Passed, 0 Skipped, 0 Failed)
- Concurrency Group: 4 passed on MariaDB
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Frontend: Vite Build Passed (2.58s)
- Route List: 57 routes terdaftar bersih (0 closure pada mutation/referral routes)
