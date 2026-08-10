---
id: DOC-KNOWN-ISSUES
title: "Known Issues and Open Questions"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Known Issues and Open Questions

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
