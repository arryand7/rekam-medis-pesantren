---
id: DOC-FIRST-STAGING-DEPLOYMENT-RUNBOOK
title: "First Staging Deployment Runbook"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# First Staging Deployment Runbook

Runbook ini desain eksekusi; Phase 5D tidak mengeksekusi deployment.

1. Catat operator, change ticket, commit/tag, waktu mulai dan rollback owner.
2. Validasi checklist server, DNS/TLS, secret store, backup destination dan maintenance communication.
3. Ambil backup database serta private storage; verifikasi checksum dan kemampuan baca archive.
4. Checkout commit yang disetujui ke release directory baru; jangan build di directory aktif.
5. Jalankan `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader` dan build frontend dari lockfile.
6. Pasang `.env` staging dari secret manager. Pastikan debug/demo seed/Gate/Attendance tetap disabled sampai validasi masing-masing selesai.
7. Jalankan `php artisan migrate --force`, kemudian `optimize:clear`, `config:cache`, `route:cache`, `view:cache`.
8. Alihkan symlink/current release secara atomik; restart PHP worker sesuai platform.
9. Aktifkan satu scheduler. Aktifkan queue supervisor hanya bila queue asinkron digunakan.
10. Verifikasi `/up`, `/health`, `/health/ready`, login lokal, RBAC multi-role, dokumen privat, audit dan outbox fake.
11. Aktifkan serta validasi Gate dan Attendance satu per satu menggunakan checklist integrasi. Jangan mengaktifkan keduanya sekaligus.
12. Catat PASS/FAIL, timestamp server dan bukti yang sudah disensor. Jika exit criteria gagal, jalankan rollback.

Tidak boleh menjalankan seeder demo pada staging.
