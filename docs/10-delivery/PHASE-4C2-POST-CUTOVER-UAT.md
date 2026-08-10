---
id: DOC-PHASE-4C2-POST-CUTOVER-UAT
title: "Phase 4C2 Post-Cutover UAT & Canary Validation Protocol"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C2 Post-Cutover UAT & Canary Validation Protocol

## 1. Ikhtisar Protokol Validasi Produksi

Dokumen ini mendefinisikan skenario verifikasi smoke test dan canary pasca rilis produksi untuk memastikan seluruh fungsionalitas inti, integrasi identitas, dan perlindungan privasi data rekam medis beroperasi dengan sempurna.

## 2. Skenario UAT & Canary Validation

| Area Sistem | Skenario Validasi | Kriteria Keberhasilan | Target Pelaksanaan |
|---|---|---|:---:|
| **Core Liveness & Readiness** | `GET /health` & `GET /health/ready` | HTTP 200, DB connected, cache operational, storage writable, zero secrets leaked | Segera setelah Symlink Switch |
| **Authentication Canary** | Login via Gate SSO (`/login`) | OIDC flow sukses, state/nonce valid, session regenerated, dashboard render | Setelah Step 3 Gate SSO ON |
| **Entitlement Enforcement** | Akses pengguna `allowed` vs `not_assigned` | Status `allowed` dapat login, status lain ditolak HTTP 302/403 dengan log audit | Setelah Step 3 Gate SSO ON |
| **Role Matrix Boundary** | Akses teknis vs medis vs asrama | Admin teknis tidak dapat melihat data medis pasien; pembina asrama hanya melihat disposisi | Setelah Step 3 Gate SSO ON |
| **Sync Apply Canary** | Gate User Sync Run | Dry-run summary akurat, apply update authoritative fields tanpa menyentuh data klinis | Setelah Step 4 Sync Apply ON |
| **Private Document Download** | Unduh surat rujukan & kepulangan | Hanya role berwenang yang dapat mengunduh; direct file access diblokir (404/403) | Setelah Step 1 Core App ON |
| **Attendance Canary** | Pengiriman disposisi istirahat | 1 event disposisi terkirim dengan zero clinical keys; outbox status `acknowledged` | Setelah Step 6 Absensi ON |
| **Queue & Outbox Monitor** | Worker & Dead-letter inspection | Event outbox diproses worker tanpa error; failed delivery backoff & dead-letter siap | Pasca Cutover |
| **Log Stabilization Window** | Pemantauan log 15–30 menit | Nol HTTP 5xx, nol duplicate identity errors, nol token/password leaks | Pasca Cutover |

## 3. Data Integrity Invariants Pasca-Cutover

1. `duplicate gate_user_id = 0`
2. `duplicate patient_number = 0`
3. `duplicate referral_number = 0`
4. `negative medicine stock = 0`
5. `orphan private documents = 0`
6. `unexpected mass deactivations = 0`
