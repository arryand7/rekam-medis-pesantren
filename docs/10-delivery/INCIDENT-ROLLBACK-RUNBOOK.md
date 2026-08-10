---
id: DOC-INCIDENT-ROLLBACK-RUNBOOK
title: "Incident Response & Emergency Rollback Runbook"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Incident Response & Emergency Rollback Runbook

## 1. Matriks Kegagalan & Respon Cepat

| Skenario Insiden | Deteksi & Gejala | Aksi Otomatis | Aksi Manual Operator | Trigger Rollback |
|---|---|---|---|---|
| **Gate SSO IdP Gangguan** | Lonjakan HTTP 5xx saat SSO, pesan OIDC timeout | User diarahkan ke halaman login dengan opsi fallback | Nonaktifkan SSO: `GATE_SSO_ENABLED=false` lalu `php artisan config:cache` | Jika IdP mati > 15 menit |
| **Integrasi Absensi Gagal** | Event outbox menumpuk di status `failed`/`dead_letter` | Backoff eksponensial otomatis, dead-letter setelah 5x | Monitor `/integration/outbox`, lakukan manual retry setelah upstream pulih | Jika upstream down lama, nonaktifkan flag `ATTENDANCE_INTEGRATION_ENABLED=false` |
| **Database Overload / Concurrency Lock** | Slow queries, query timeouts | Timeout koneksi setelah 5 detik | Periksa running queries via `SHOW FULL PROCESSLIST`, bunuh query bermasalah | Jika DB crash berulang |
| **Aplikasi Baru Mengalami Fatal Crash** | HTTP 500 menyeluruh, health check gagal | Health probe mengembalikan HTTP 503 | Jalankan symlink rollback ke versi sebelumnya | Jika crash > 1% total request |
| **Private Document Permission Denied** | Staf medis tidak dapat mengunduh surat rujukan | Audit log mencatat error disk storage | Periksa permission direktori `storage/app/private` (wajib chmod 750) | Tidak perlu rollback app, cukup perbaiki izin folder |

## 2. Prosedur Eksekusi Emergency Rollback

### Langkah 1: Isolasi Layanan (Optional Maintenance Mode)
```bash
php artisan down --secret="incident-response-mode"
```

### Langkah 2: Alihkan Symlink ke Rilis Stabil
```bash
ln -sfn /var/www/poskestren/releases/<PREVIOUS_STABLE_SHA> /var/www/poskestren/current
```

### Langkah 3: Rebuild Cache & Restart Daemons
```bash
cd /var/www/poskestren/current
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.4-fpm
sudo supervisorctl restart poskestren-worker:*
```

### Langkah 4: Verifikasi Pemulihan
```bash
curl -f http://127.0.0.1/health
curl -f http://127.0.0.1/health/ready
php artisan up
```
