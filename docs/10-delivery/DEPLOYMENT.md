---
id: DOC-DEPLOYMENT
title: "Deployment"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Deployment

## Target

Ubuntu + Nginx + PHP-FPM + MariaDB + Redis + Supervisor.

## Pipeline

1. Build dan test di CI.
2. Backup sebelum migration berisiko.
3. Deploy release directory.
4. Composer install `--no-dev`.
5. Frontend build artifact.
6. Link environment/storage.
7. Run migration `--force`.
8. Cache config/routes/views.
9. Restart queue worker.
10. Health check.
11. Smoke test authorization.
12. Switch release.
13. Monitor.

## Database

- Tidak menggunakan `migrate:fresh`.
- Migration harus backward-compatible bila memungkinkan.
- Rollback plan wajib.
- Data migration terpisah dan idempotent.

## Secret

Disimpan di server/secret store, tidak dalam repositori.
