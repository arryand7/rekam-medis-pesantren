---
id: DOC-PHASE-4C-DEPLOYMENT-RUNBOOK
title: "Phase 4C Production Deployment & Cutover Runbook"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4C Production Deployment & Cutover Runbook

## 1. Ikhtisar Rilis & Cutover Bertahap

Deployment POSKESTREN Health ke lingkungan produksi dilakukan dengan pola rilis atomik (*symlink switching*) dan aktivasi bertahap 6 langkah untuk memastikan nol *clinical disruption*.

## 2. Struktur Direktori Rilis Atomik

```text
/var/www/poskestren/
├── current -> /var/www/poskestren/releases/20260810_160000_dd5798f
├── shared/
│   ├── .env
│   └── storage/
│       ├── app/
│       │   ├── private/
│       │   │   ├── referrals/
│       │   │   ├── referral_external/
│       │   │   └── discharges/
│       │   └── public/
│       ├── framework/
│       │   ├── cache/
│       │   ├── sessions/
│       │   └── views/
│       └── logs/
└── releases/
    ├── 20260810_150000_991776c/
    └── 20260810_160000_dd5798f/
```

## 3. Langkah-Langkah Deployment Produksi (Step-by-Step)

### Tahap 1: Build Candidate & Quality Gate
1. Clone repositori ke direktori release baru:
   ```bash
   git clone --depth 1 --branch main <REPO_URL> /var/www/poskestren/releases/$RELEASE_ID
   cd /var/www/poskestren/releases/$RELEASE_ID
   ```
2. Pasang dependensi backend & frontend:
   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
   npm ci
   npm run build
   ```
3. Link shared storage dan environment:
   ```bash
   ln -s /var/www/poskestren/shared/.env .env
   rm -rf storage
   ln -s /var/www/poskestren/shared/storage storage
   ```
4. Jalankan optimasi cache:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Tahap 2: Pre-Cutover Backup & Migration
1. Lakukan database snapshot pre-cutover:
   ```bash
   mysqldump --single-transaction --routines --triggers --hex-blob poskestren_health_prod | gzip > /backups/db/poskestren_db_precutover_$(date +%Y%m%d_%H%M%S).sql.gz
   ```
2. Jalankan migrasi database aman secara terkendali:
   ```bash
   php artisan migrate --force
   ```

### Tahap 3: Atomic Symlink Switch & Process Reload
1. Alihkan symlink `current`:
   ```bash
   ln -sfn /var/www/poskestren/releases/$RELEASE_ID /var/www/poskestren/current
   ```
2. Reload PHP-FPM dan restart queue workers:
   ```bash
   sudo systemctl reload php8.4-fpm
   sudo supervisorctl restart poskestren-worker:*
   ```
3. Verifikasi liveness dan readiness probe:
   ```bash
   curl -f http://127.0.0.1/health
   curl -f http://127.0.0.1/health/ready
   ```

### Tahap 4: Aktivasi Bertahap Integrasi Eksternal (Strict Order)
1. **Step 1 (Core App)**: `GATE_SSO_ENABLED=false`, `ATTENDANCE_INTEGRATION_ENABLED=false`. Verifikasi modul dasar berjalan normal.
2. **Step 2 (Gate SSO Probe)**: Uji konektivitas TLS dan endpoint OIDC Gate.
3. **Step 3 (Gate SSO Activation)**: Aktifkan `GATE_SSO_ENABLED=true`, `GATE_CLIENT_DRIVER=http`. Uji login 1 akun staf resmi.
4. **Step 4 (Gate Sync Apply)**: Aktifkan `GATE_SYNC_APPLY_ENABLED=true`. Jalankan Dry-Run dahulu, lalu Apply setelah review aman.
5. **Step 5 (Attendance Probe)**: Uji status endpoint absensi `/integration/attendance/status`.
6. **Step 6 (Attendance Activation)**: Aktifkan `ATTENDANCE_INTEGRATION_ENABLED=true`, `ATTENDANCE_INTEGRATION_DRIVER=http`. Kirim 1 disposisi operasional tervalidasi.
