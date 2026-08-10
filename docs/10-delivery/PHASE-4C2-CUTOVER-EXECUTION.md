---
id: DOC-PHASE-4C2-CUTOVER-EXECUTION
title: "Phase 4C2 Controlled Cutover Execution Plan & Readiness State"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C2 Controlled Cutover Execution Plan & Readiness State

## 1. Status Otorisasi Cutover

- **Frasa Otorisasi Wajib**: `SETUJUI CUTOVER PRODUCTION POSKESTREN`
- **Status Saat Ini**: **AWAITING-PRODUCTION-AUTHORIZATION**
- **Kepatuhan Guardrails**: Tidak ada mutasi database produksi, perubahan file `.env`, deployment release, atau pengaktifan feature flag yang dilakukan sebelum frasa otorisasi resmi diberikan oleh pengguna.

## 2. Parameter Rilis & Metadata

| Parameter Rilis | Nilai Terverifikasi | Status |
|---|---|:---:|
| Candidate Commit SHA | `ee7d4aa` (`chore(production): complete Phase 4C deployment hardening and go-live runbook`) | READY |
| Baseline Release SHA | `dd5798f` (`phase-4b-complete`) | READY |
| Working Tree | Clean (0 modified, 0 untracked non-prompt files) | READY |
| Test Suite Passing | 174 tests, 682 assertions (100% Passed) | READY |
| Linters & Analysis | Pint PASSED, PHPStan Level 5 (0 errors), Vite PASSED | READY |
| Database Engine | MariaDB 10.4.28+ (InnoDB, REPEATABLE-READ) | READY |
| Total Migrations | 56 migrations (`SAFE_ONLINE`, 0 pending, 0 destructive) | READY |
| Private Storage Disks | `referral_documents`, `referral_external_documents`, `discharge_documents` (chmod 750) | READY |
| Health Probes | `/health` (Liveness) & `/health/ready` (Readiness) HTTP 200 | READY |

## 3. Protokol Eksekusi Cutover 6 Langkah (Bila Diotorisasi)

```text
[Step 1: Core App Deployment]
   ├── Ambil fresh snapshot MariaDB & Private Storage
   ├── Deploy release symlink (current -> releases/<RELEASE_ID>)
   ├── Reload PHP-FPM & Supervisor Queue Workers
   └── Feature Flags: GATE_SSO_ENABLED=false, ATTENDANCE_INTEGRATION_ENABLED=false
          │
          ▼
[Step 2: Gate Production Probe]
   ├── Uji TLS & OIDC Discovery: https://gate.sabira.id/.well-known/openid-configuration
   └── Verifikasi Endpoint Token, UserInfo, dan Application Entitlement
          │
          ▼
[Step 3: Gate SSO Activation (Canary Only)]
   ├── Ubah flag: GATE_SSO_ENABLED=true, GATE_CLIENT_DRIVER=http
   ├── Rebuild config cache (php artisan config:cache)
   └── Validasi login 1 akun staf terotorisasi
          │
          ▼
[Step 4: Gate Sync Apply (Dry-Run First)]
   ├── Ubah flag: GATE_SYNC_APPLY_ENABLED=true
   ├── Jalankan Dry-Run & periksa summary (new/matched/changed/conflicts/source_missing)
   └── Memerlukan otorisasi tambahan: "SETUJUI APPLY SYNC GATE PRODUCTION"
          │
          ▼
[Step 5: Attendance Production Probe]
   ├── Keep ATTENDANCE_INTEGRATION_ENABLED=false
   └── Uji endpoint upstream: https://absensi.sabira.id/api/v1/health-dispositions/status
          │
          ▼
[Step 6: Attendance Activation (Separate Approval)]
   ├── Memerlukan otorisasi: "SETUJUI AKTIVASI ABSENSI PRODUCTION"
   ├── Ubah flag: ATTENDANCE_INTEGRATION_ENABLED=true, ATTENDANCE_INTEGRATION_DRIVER=http
   └── Kirim 1 event canary tervalidasi privasi (zero clinical keys)
```

## 4. Rencana Rollback Cepat (Emergency Guard)

1. **Feature Rollback**: Nonaktifkan flag di `.env` dan jalankan `php artisan config:cache`.
2. **Release Rollback**: Alihkan symlink `current` ke SHA `dd5798f` dan reload PHP-FPM.
3. **Database Restore**: Pulihkan database dari snapshot pre-cutover jika terjadi inkonsistensi skema.
