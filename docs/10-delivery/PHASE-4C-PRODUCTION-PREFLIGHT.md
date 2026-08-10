---
id: DOC-PHASE-4C-PRODUCTION-PREFLIGHT
title: "Phase 4C Production Environment Preflight & Audit"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C Production Environment Preflight & Audit

## 1. Ikhtisar Infrastruktur & Runtime Produksi

Dokumen ini memverifikasi kesiapan seluruh komponen infrastruktur server, runtime PHP, web server, database MariaDB, sistem antrean, dan parameter keamanan sebelum proses cutover rilis produksi.

## 2. Matriks Audit Kesiapan Produksi

| Komponen Infrastruktur | Standar / Target Produksi | Status Audit | Tindakan / Verifikasi |
|---|---|:---:|---|
| **Sistem Operasi** | Linux (Debian 12 / Ubuntu 24.04 LTS / RHEL 9) | READY | Kernel modern, POSIX compliant |
| **PHP Runtime** | PHP 8.3.x / 8.4.x (CLI & FPM) | READY | Ext: `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl` |
| **OPcache** | `opcache.enable=1`, `opcache.validate_timestamps=0` | READY | Di-preload pada production |
| **Composer** | Composer 2.7+ | READY | `--no-dev --optimize-autoloader --prefer-dist` |
| **Node.js & Vite** | Node 20 LTS, Vite 8.x | READY | Build assets ke `public/build` manifest |
| **Database Engine** | MariaDB 10.4.28+ (InnoDB) | READY | `innodb_buffer_pool_size` optimal, `REPEATABLE-READ` |
| **Database Identity** | `poskestren_health_prod` (terisolasi dari staging) | READY | Verifikasi nama DB eksplisit sebelum migrasi |
| **Queue Worker** | Supervisor / Systemd (`queue:work --tries=3 --timeout=90`) | READY | Autostart & restart otomatis pada worker crash |
| **Task Scheduler** | Cron daemon (`* * * * * php artisan schedule:run >> /dev/null 2>&1`) | READY | Tepat satu sumber scheduler sistem |
| **Cache & Session** | Redis / Database Session (Persistent) | READY | Sesi bertahan lintas redirect OIDC Gate |
| **Private Document Storage** | `storage/app/private/` (chmod 750) | READY | Terisolasi dari `public/`, tidak diekspos web server |
| **Web Server / Reverse Proxy** | Nginx / Caddy dengan TLS 1.3 | READY | HSTS enabled, redirect HTTP ke HTTPS |
| **Trusted Proxies** | `TrustProxies` middleware membaca `X-Forwarded-Proto` | READY | Mencegah mixed content redirects |
| **Security Headers** | `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` | READY | Mencegah clickjacking dan MIME sniffing |
| **Production Secrets** | `.env` terenkripsi / secret manager terpisah | READY | Zero secrets di repositori git |
| **Backup Storage** | Automated encrypted offsite snapshot | READY | Backup DB dan private storage terpisah |
| **Health Probes** | `/health` (Liveness) & `/health/ready` (Readiness) | READY | Bebas dari kebocoran credential / stack trace |

## 3. Status Konfigurasi Awal Feature Flag

```ini
# Konfigurasi Awal Produksi (Step 1 Cutover: Core App Healthy, Integrations OFF)
GATE_SSO_ENABLED=false
GATE_CLIENT_DRIVER=fake
GATE_SYNC_APPLY_ENABLED=false
GATE_WEBHOOK_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
BREAK_GLASS_ENABLED=false
```

## 4. Evaluasi Kesiapan

Semua komponen preflight berstatus **READY**. Tidak ditemukan *Critical Blocker*. Lingkungan produksi siap menerima rilis atomic candidate.
