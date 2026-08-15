---
id: DOC-STAGING-SERVER-REQUIREMENTS
title: "Staging Server Requirements"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Staging Server Requirements

- Linux server dengan PHP 8.3+ beserta extension Laravel/MariaDB, Composer 2, Node/npm untuk build pipeline atau artefak build tepercaya, dan MariaDB kompatibel.
- Document root hanya ke `public/`; user proses web memiliki write terbatas ke `storage/` dan `bootstrap/cache/`.
- TLS valid, HTTPS redirect, DNS staging, dan `TRUSTED_PROXIES` berisi IP/CIDR aktual—bukan wildcard.
- Database user least privilege, backup destination terenkripsi, private storage tidak berada di public web root.
- Cron `* * * * * php artisan schedule:run` dengan working directory release; hanya satu scheduler aktif per environment.
- Supervisor queue hanya bila queue asinkron digunakan. Scheduler tetap wajib untuk integration outbox.
- Shared session/cache database atau Redis apabila lebih dari satu app instance.
- Secret manager untuk `APP_KEY`, DB, Gate dan Attendance credentials; `.env` permission minimum.
- Log rotation, disk alert, clock synchronization, backup retention dan restore drill disepakati operator.

Nilai kapasitas CPU/RAM/disk, topology, hostname, proxy CIDR dan retensi: **[PERLU DIKONFIRMASI]** sebelum deployment staging pertama.
