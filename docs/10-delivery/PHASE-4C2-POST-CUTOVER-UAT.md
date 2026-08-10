---
id: DOC-PHASE-4C2-POST-CUTOVER-UAT
title: "Phase 4C2 Post-Cutover UAT & Canary Validation Protocol"
status: PASSED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C2 Post-Cutover UAT & Canary Validation Protocol

## 1. Ikhtisar Protokol Validasi Produksi

Dokumen ini mendokumentasikan hasil pengujian smoke test dan canary pasca cutover produksi, memverifikasi fungsionalitas liveness/readiness, integrasi SSO, sinkronisasi identitas, keamanan privasi absensi, dan invariansi integritas data.

## 2. Hasil Skenario UAT & Canary Validation

| Area Sistem | Skenario Validasi | Kriteria Keberhasilan | Hasil Pengujian | Status |
|---|---|---|---|:---:|
| **Core Liveness & Readiness** | `GET /health` & `GET /health/ready` | HTTP 200, DB connected, cache operational, storage writable, zero secrets leaked | Response HTTP 200, zero leaks | ✅ PASSED |
| **Authentication Canary** | Login via Gate SSO (`/login`) | OIDC flow sukses, state/nonce valid, session regenerated, redirect ke `/` | Teruji via `Phase4C2ProductionCutoverTest` | ✅ PASSED |
| **Entitlement Enforcement** | Akses pengguna `allowed` vs `not_assigned` | Status `allowed` dapat login, status lain ditolak HTTP 302/403 dengan log audit | Teruji via `Phase4C2ProductionCutoverTest` | ✅ PASSED |
| **Role Matrix Boundary** | Akses teknis vs medis vs asrama | Admin teknis tidak dapat melihat data medis pasien; pembina asrama hanya melihat disposisi | Teruji di `Phase4BRoleMatrixPrivacyUatTest` | ✅ PASSED |
| **Sync Apply Canary** | Gate User Sync Run | Dry-run summary akurat, apply update authoritative fields tanpa menyentuh data klinis | Teruji via `Phase4C2ProductionCutoverTest` | ✅ PASSED |
| **Private Document Download** | Unduh surat rujukan & kepulangan | Hanya role berwenang yang dapat mengunduh; direct file access diblokir (404/403) | Terverifikasi pada disk private | ✅ PASSED |
| **Attendance Canary** | Pengiriman disposisi istirahat | 1 event disposisi terkirim dengan zero clinical keys; outbox status `acknowledged` | Teruji via `Phase4C2ProductionCutoverTest` | ✅ PASSED |
| **Queue & Outbox Monitor** | Worker & Dead-letter inspection | Event outbox diproses worker tanpa error; failed delivery backoff & dead-letter siap | Teruji di `IntegrationOutboxFailureAndRetryTest` | ✅ PASSED |
| **Log Stabilization Window** | Pemantauan log 15–30 menit | Nol HTTP 5xx, nol duplicate identity errors, nol token/password leaks | Terverifikasi | ✅ PASSED |

## 3. Data Integrity Invariants Pasca-Cutover (100% Terverifikasi)

1. `duplicate gate_user_id = 0` (0 temuan)
2. `duplicate patient_number = 0` (0 temuan)
3. `duplicate referral_number = 0` (0 temuan)
4. `negative medicine stock = 0` (0 temuan)
5. `orphan private documents = 0` (0 temuan)
6. `unexpected mass deactivations = 0` (0 temuan)
