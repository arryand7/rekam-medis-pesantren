---
id: DOC-PRODUCTION-GO-LIVE-CHECKLIST
title: "Production Go-Live Verification Checklist"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Go-Live Verification Checklist

## 1. Daftar Periksa Kesiapan Go-Live (Go-Live Gate)

| Kategori | Item Periksa | Verifikasi Teknis | Status |
|---|---|---|:---:|
| **Security & Config** | `APP_DEBUG=false` | Terverifikasi di `.env.production` | ✅ READY |
| | `SESSION_SECURE_COOKIE=true` & `HttpOnly=true` | Terverifikasi di konfigurasi sesi | ✅ READY |
| | Trusted Proxies terkonfigurasi | Header proto & forwarded-for terbaca benar | ✅ READY |
| | Zero Secrets in Repository | `git diff` dan audit repo bersih | ✅ READY |
| | Private Document Storage terisolasi | Path `storage/app/private/` di luar web root | ✅ READY |
| **Database** | Snapshot backup terverifikasi | Dump SQL terkompresi non-zero & teruji | ✅ READY |
| | Migrasi database aman | 56 migrasi tervalidasi `SAFE_ONLINE` | ✅ READY |
| | Concurrency & row locks | Invariant MariaDB tervalidasi | ✅ READY |
| **IAM / Gate SSO** | OIDC Client ID & Secret terdaftar | Diverifikasi pada Gate Production IdP | ✅ READY |
| | Redirect URI cocok presisi | `https://health.sabira.id/auth/gate/callback` | ✅ READY |
| | Application Entitlement aktif | Enforce `allowed` status | ✅ READY |
| **Integrasi Absensi** | Privacy payload guard aktif | Zero data klinis ke endpoint absensi | ✅ READY |
| | Outbox retry & dead-letter siap | Backoff policy & manual retry teruji | ✅ READY |
| **Workers & Cron** | Queue worker autostart & restart | Supervisor daemon aktif | ✅ READY |
| | Scheduler cron aktif | Tepat satu cron job terpasang | ✅ READY |
| **Observability** | Health probes `/health` & `/health/ready` | HTTP 200 responses tanpa data sensitif | ✅ READY |
| | Log rotation & structured logging | Correlation ID tercatat | ✅ READY |
| **Rollback Plan** | Symlink rollback & feature flags | Prosedur drill terdokumentasi | ✅ READY |

## 2. Kriteria Otorisasi Go-Live

Persetujuan Go-Live resmi diberikan bila seluruh item di atas berstatus **READY** dan telah ditandatangani oleh Lead Architect.
