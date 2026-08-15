---
id: DOC-PHASE-5D-ENV-MATRIX
title: "Phase 5D Environment Variable Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D Environment Variable Matrix

Nilai rahasia tidak boleh disimpan di Git. `.env.example` hanya menyediakan nama dan default aman.

| Kelompok | Wajib staging | Default aman / aturan |
|---|---|---|
| App | `APP_KEY`, `APP_URL`, `APP_VERSION` | `APP_ENV=staging`, `APP_DEBUG=false`, timezone Asia/Jakarta |
| Proxy/TLS | `TRUSTED_PROXIES` | hanya IP/CIDR proxy yang dikendalikan; kosong bila direct connection |
| Database | `DB_*` | akun least privilege, charset/collation utf8mb4; `DB_SOCKET` kosong kecuali host memang memakai socket |
| Session/cache | `SESSION_*`, `CACHE_STORE`, `DB_CACHE_*` | secure cookie wajib true di HTTPS; database/Redis shared untuk multi-instance |
| Queue/outbox | `QUEUE_CONNECTION`, `DB_QUEUE_*`, `INTEGRATION_OUTBOX_*` | scheduler wajib; worker hanya wajib bila connection dan workload queue asinkron dipakai |
| Logging | `LOG_CHANNEL`, `LOG_LEVEL` | `LOG_LEVEL=warning` atau kebijakan operator; jangan log payload medis/secret |
| Gate | seluruh `GATE_*` | disabled + fake sampai nilai staging disetujui; secret wajib secret store |
| Break-glass | seluruh `BREAK_GLASS_*` | disabled; aktivasi memerlukan SOP/approval terpisah |
| Attendance | seluruh `ATTENDANCE_INTEGRATION_*` | disabled + fake sampai endpoint/API key sandbox disetujui |
| Pharmacy | `PHARMACY_*` | nilai kebijakan harus dikonfirmasi pemilik SOP, bukan ditentukan aplikasi |
| Filesystem/mail | `FILESYSTEM_DISK`, `MAIL_*` | file medis tetap di private disks; mail `log` tidak untuk staging nyata |

## Preflight wajib

1. Buat `.env` di server dari secret manager, bukan menyalin credential lokal.
2. Jalankan `php artisan config:show app`, `config:show gate`, dan `config:show integrations` dengan output yang sudah disensor.
3. Pastikan `APP_DEBUG=false`, demo seed false, integration disabled sebelum credential tervalidasi.
4. Jalankan `config:cache` lalu ulangi health check dan smoke login.
