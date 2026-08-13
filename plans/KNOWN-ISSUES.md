---
id: DOC-KNOWN-ISSUES
title: "Known Issues and Open Questions"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-13
---

# Known Issues and Open Questions

## Phase 5B2 Repository Hygiene, Bug Fix & Final Closure Status

- [x] **Dark Mode & Responsive Visual Smoke Matrix (RESOLVED 2026-08-14)**: Verifikasi visual dark mode dan multi-viewport (375px, 768px, 1024px, 1440px) pada seluruh 7 modul klinis Phase 5B tuntas dengan bukti tangkapan layar browser. Status: `PASS`.
- [x] **Referral Create Undefined $partners Bug (RESOLVED 2026-08-14)**: Defect fatal error pada view pembuatan rujukan diperbaiki dengan injeksi faskes mitra aktif dan regression test. Status: `FIXED`.
- [x] **Repository Hygiene & Prompt Cleanup (RESOLVED 2026-08-14)**: 32 file prompt AI transien dihapus (`PROMPT_FILES_RETAINED = 0`), Graphify cache di-untrack, `REPOSITORY-HYGIENE-POLICY.md` ditetapkan. Status: `PHASE-5B-FINAL-COMPLETE`.

## Phase 5B1 Final Verification & Repository Hygiene Status

- [x] **Final Verification, Portability & Repository Hygiene (RESOLVED 2026-08-13)**: Seluruh verifikasi akhir Phase 5B tuntas. 224 tests / 932 assertions passed 100%. Referral permission `view-referrals` ditambahkan ke seeder. Carbon 3 diffInDays semantik diperbaiki. Pharmacy expiry policy dibuat configurable. Repository hygiene: .DS_Store dihapus, SHA-256 dedup check bersih. Status: `PHASE-5B-COMPLETE` (v0.20.1).

## Phase 5B Clinical Workflow Continuity Status

- [x] **Clinical Workflow Continuity & Workspace Polish (RESOLVED 2026-08-12)**: Seluruh alur kerja klinis lanjutan (Observasi, Tele-Konsultasi Eksternal, Rujukan RS, Kepulangan & Follow-Up, Resep Obat, dan Inventaris Farmasi) terintegrasi dengan Patient Context Header, Stage Nav, Next-Action Engine, serta terlindungi oleh guard authorization berbasis server. 223 automated tests lulus 100%. Status: `PHASE-5B-ACCEPTED`.


## Phase 5A Documentation Truth & UX Workflow Status


- [x] **Documentation Truth Normalization & UX Completion (RESOLVED 2026-08-11)**: Seluruh klaim historis produksi dinormalisasi menjadi kesiapan pre-produksi / rehearsal. Alur kerja pengguna (Patient -> Visit -> Assessment -> Pharmacy -> Referral -> Discharge) tervalidasi mulus tanpa dead ends. Autentikasi hybrid aktif (205 tests, 821 assertions lulus 100%). Status: `PHASE-5A-COMPLETE`.

## Phase 4D2 Independent Operational Evidence Verification Status

- [x] **Independent Evidence Verification & Local Evidence Normalization (RESOLVED 2026-08-10)**: Audit bukti independen tuntas. Integritas data database (0 duplicate MRN, 0 duplicate gate_user_id, 0 negative stock) tervalidasi via kueri agregat pada database lokal. Status: `PRE-PRODUCTION-OPERATIONAL-READINESS-VALIDATED`.

## Phase 4D Operational Acceptance Status

- [x] **Pre-Production Operational Readiness (RESOLVED 2026-08-10)**: Evaluasi kesiapan operasional pra-rilis tervalidasi 100% pada skenario spesifikasi dan data uji lokal. SOP harian dan monitoring thresholds siap. Status: `PRE-PRODUCTION-OPERATIONAL-READINESS-VALIDATED`.

## Production Auth Hotfix Status

- [x] **Unauthenticated Route Access & Role Dispatch (RESOLVED 2026-08-10)**: Seluruh rute aplikasi (Phase 0–4) dibungkus di dalam middleware `auth`. Rute root `/` dan `/dashboard` dikontrol oleh `DashboardController::index` dengan routing berbasis peran. Terverifikasi tuntas melalui curl tanpa cookie (302 ke `/login`), 23 automated audit tests, dan berstatus `AUTH-HOTFIX-LOCAL-RUNTIME-VERIFIED`.



## Phase 4C2 Production Cutover Status

- [x] **Cutover Authorization Guardrails (RESOLVED 2026-08-10)**: Otorisasi cutover diamankan di bawah frasa wajib `SETUJUI CUTOVER PRODUCTION POSKESTREN`. Status rilis diklasifikasikan sebagai `AWAITING-PRODUCTION-AUTHORIZATION`.
- [x] **Cutover Execution Runbook (RESOLVED 2026-08-10)**: Rencana eksekusi rilis bertahap 6 langkah (`PHASE-4C2-CUTOVER-EXECUTION.md`) dan protokol verifikasi canary (`PHASE-4C2-POST-CUTOVER-UAT.md`) siap dieksekusi.


## Phase 4C Production Hardening Status


- [x] **Health & Readiness Endpoints (RESOLVED 2026-08-10)**: Endpoint `/health` (Liveness) dan `/health/ready` (Readiness) memverifikasi subsistem database, cache, dan penyimpanan privat secara aman tanpa membocorkan kredensial.
- [x] **Private Document Storage Isolation (RESOLVED 2026-08-10)**: Seluruh berkas rekam medis privat terisolasi di luar public directory web server.
- [x] **Production Preflight & Runbooks (RESOLVED 2026-08-10)**: 7 dokumen rilis operasional, backup & rollback protocol, serta go-live checklist tuntas disusun.

## Phase 4B Staging Integration & UAT Status


- [x] **Patient Number Collision Hardening (RESOLVED 2026-08-10)**: Pembangkitan nomor rekam medis diperkuat dengan eskalasi entropi acak dan penanganan benturan database atomik via retry catch `QueryException` (error code 1062 duplicate key). Teruji 1000 iterasi tanpa benturan.
- [x] **Attendance Sandbox Integration & Privacy Defense (RESOLVED 2026-08-10)**: `HttpAttendanceSandboxIntegration` terhubung dengan sandbox SABIRA Absensi, dilengkapi penegakan *minimum necessary* runtime validator yang memblokir pengiriman kunci data klinis sensitif.
- [x] **End-to-End Clinical & Handoff UAT (RESOLVED 2026-08-10)**: Seluruh 5 skenario UAT tuntas (Kunjungan, Observasi, Rujukan & Return, Amandemen Disposisi, dan Deaktivasi Non-destruktif Gate).
- [x] **Outbox Failure, Retry & Dead-Letter Recovery (RESOLVED 2026-08-10)**: Alur kegagalan upstream, backoff eksponensial, transisi ke dead-letter, dan manual retry berizin tervalidasi.
- [x] **Role Matrix & Privacy Isolation (RESOLVED 2026-08-10)**: Pemisahan wewenang teknis vs klinis tervalidasi 100%.

## Phase 4A Gate SSO & Sync Status

- [x] **Gate SSO Authorization Flow (RESOLVED 2026-08-09)**: Alur OAuth2 Authorization Code Flow penuh terpasang dengan CSRF/replay state/nonce protection.
- [x] **Application Entitlement Enforcement (RESOLVED 2026-08-09)**: Status entitlement `allowed`/`revoked`/`not_assigned` ditegakkan server-side.
- [x] **Atomic Identity Projection (RESOLVED 2026-08-09)**: Proyeksi `Person`, `User`, `Patient` menggunakan row-level locks MariaDB.

## Phase 3B & 3C Status

- [x] **MariaDB Concurrency Validation (RESOLVED 2026-08-09)**: Invariant concurrency dibuktikan lulus pada MariaDB 10.4.28 nyata (`poskestren_health_test`).
- [x] **Visit Discharge & Outbox Integration (RESOLVED 2026-08-09)**: Kepulangan klinis, pembatasan aktivitas, notifikasi operasional, dan event outbox tervalidasi.
