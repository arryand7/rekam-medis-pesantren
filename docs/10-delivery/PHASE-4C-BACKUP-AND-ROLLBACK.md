---
id: DOC-PHASE-4C-BACKUP-AND-ROLLBACK
title: "Phase 4C Pre-Cutover Backup & Rollback Protocol"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C Pre-Cutover Backup & Rollback Protocol

## 1. Ikhtisar Protokol Backup

Sebelum menjalankan proses migrasi database atau cutover rilis produksi baru, seluruh komponen aplikasi, data, konfigurasi, dan dokumen privat wajib di-backup secara terverifikasi.

## 2. Rincian Snapshot Pre-Cutover

| Komponen Backup | Perintah / Sumber | Lokasi Penyimpanan | Verifikasi Kualitas |
|---|---|---|---|
| **Database MariaDB** | `mysqldump --single-transaction --routines --triggers --hex-blob poskestren_health_prod > db_backup.sql` | `/backups/db/poskestren_db_precutover_$(date +%Y%m%d_%H%M%S).sql.gz` | Ukuran non-zero, checksum SHA-256 dicatat, tabel lengkap |
| **Private Document Storage** | `tar -czf private_docs.tar.gz -C storage/app private` | `/backups/storage/poskestren_private_$(date +%Y%m%d_%H%M%S).tar.gz` | Verifikasi arsip `tar -tzf`, integritas metadata |
| **Environment Configuration** | Salinan `.env.production` | `/backups/config/.env.prod.bak_$(date +%Y%m%d_%H%M%S)` | Terkunci hak akses chmod 600 |
| **Nginx Web Server Config** | `/etc/nginx/sites-available/poskestren` | `/backups/nginx/poskestren_nginx.conf.bak` | Nginx `-t` syntax valid |
| **Systemd / Supervisor Workers** | `/etc/supervisor/conf.d/poskestren-worker.conf` | `/backups/supervisor/worker.conf.bak` | Syntax valid |

## 3. Strategi Rollback Bertingkat

### Tingkat 1: Feature Rollback (Zero Downtime)
Bila terjadi anomali pada salah satu integrasi eksternal (Gate SSO atau Absensi):
1. Ubah feature flag terkait menjadi `false` pada `.env`:
   ```ini
   GATE_SSO_ENABLED=false
   GATE_SYNC_APPLY_ENABLED=false
   ATTENDANCE_INTEGRATION_ENABLED=false
   ```
2. Jalankan `php artisan config:cache`.
3. Selesai (aplikasi kembali ke fallback lokal/stub tanpa memutus modul inti).

### Tingkat 2: Application Release Rollback (Atomic Symlink Switch)
Bila terjadi kegagalan pada kode aplikasi baru:
1. Alihkan symlink rilis `current` ke rilis sebelumnya:
   ```bash
   ln -sfn /var/www/poskestren/releases/<PREVIOUS_RELEASE_SHA> /var/www/poskestren/current
   ```
2. Restart PHP-FPM dan Queue Workers:
   ```bash
   sudo systemctl reload php8.4-fpm
   sudo supervisorctl restart poskestren-worker:*
   ```
3. Verifikasi endpoint `/health`.

### Tingkat 3: Database Restoration (Disaster Recovery)
Hanya dilakukan jika migrasi database gagal atau terjadi kerusakan data:
1. Aktifkan mode pemeliharaan: `php artisan down --secret="emergency-restore"`
2. Restore database dari snapshot pre-cutover:
   ```bash
   gunzip < /backups/db/poskestren_db_precutover_*.sql.gz | mariadb -u poskestren_user -p poskestren_health_prod
   ```
3. Nonaktifkan mode pemeliharaan: `php artisan up`
