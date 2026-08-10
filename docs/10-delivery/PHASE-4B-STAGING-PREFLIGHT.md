---
id: DOC-PHASE-4B-STAGING-PREFLIGHT
title: "Phase 4B Staging Environment Preflight Specification"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4B Staging Environment Preflight Specification

## 1. Ikhtisar Lingkungan Staging

Dokumen ini mendefinisikan prasyarat teknis, konfigurasi jaringan, parameter sesi/cookie, trusted proxies, dan integrasi pihak ketiga untuk lingkungan Staging POSKESTREN Health sebelum pengujian UAT.

## 2. Checklist Konfigurasi Staging

| Item | Konfigurasi Staging | Status | Catatan Keamanan |
|---|---|---|---|
| App URL | `https://health-staging.sabira.id` | READY | Wajib HTTPS |
| Reverse Proxy | Nginx / Caddy TLS Termination | READY | Forwarded headers enabled |
| Trusted Proxies | `TrustProxies` middleware configured | READY | Membaca `X-Forwarded-Proto`, `X-Forwarded-For` |
| Session Domain | `.sabira.id` atau `null` (subdomain isolated) | READY | Mencegah leakage ke domain lain |
| Secure Cookie | `SESSION_SECURE_COOKIE=true` | READY | Hanya ditransmisikan via HTTPS |
| SameSite Cookie | `SESSION_SAME_SITE=lax` | READY | Mendukung OIDC 302 redirect callback |
| HttpOnly Cookie | `true` | READY | Mencegah akses JavaScript ke session ID |
| CSRF Protection | Enabled (`VerifyCsrfToken`) | READY | State parameter + nonce pada OIDC |
| Queue Driver | `database` / `redis` (bukan `sync` di production) | READY | Worker memproses outbox asinkron |
| Scheduler | Cron running `php artisan schedule:run` | READY | Memproses outbox retry & sensus |
| Database Engine | MariaDB 10.4.28+ (InnoDB) | READY | Row-level locking & REPEATABLE-READ |
| Gate Staging URL | `https://gate-staging.sabira.id` | READY | IdP Staging terisolasi |
| Gate Driver (Staging) | `http` | READY | HTTP client dengan timeout & retry |
| Gate SSO Flag (Staging) | `GATE_SSO_ENABLED=true` | READY | Hanya aktif di staging |
| Gate Sync Apply (Staging) | `GATE_SYNC_APPLY_ENABLED=true` | READY | Hanya aktif di staging |
| Absensi Sandbox URL | `https://absensi-sandbox.sabira.id/api/v1` | READY | Endpoint sandbox terisolasi |
| Absensi Driver (Staging) | `sandbox` (`HttpAttendanceSandboxIntegration`) | READY | Memvalidasi payload dan zero clinical keys |
| Production Flags | `GATE_SSO_ENABLED=false`, `ATTENDANCE_INTEGRATION_ENABLED=false` | READY | Tetap nonaktif di konfigurasi produksi |

## 3. Isolasi Data & Kebijakan Sanitasi

1. **Synthetic Accounts**: Semua pengujian UAT menggunakan akun sintetis (`GATE-STF-*`, `GATE-SAN-*`).
2. **Zero Production DB Copy**: Database staging tidak boleh disalin mentah dari production tanpa sanitasi total.
3. **No Secret Commits**: Token, client secret, dan password tidak pernah ditulis ke repositori kode.
