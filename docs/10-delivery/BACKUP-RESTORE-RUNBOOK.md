---
id: DOC-BACKUP-RESTORE-RUNBOOK
title: "Backup and Restore Runbook"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Backup and Restore Runbook

## Backup

1. Tentukan database, release, timestamp server dan retention label secara eksplisit.
2. Gunakan client dump yang kompatibel; dump schema+data dengan transaction-consistent option. Stored routines hanya disertakan bila benar-benar dipakai dan versi server mendukungnya.
3. Archive seluruh `storage/app/private` dengan permission/metadata yang sesuai.
4. Hitung SHA-256, enkripsi dan pindahkan ke backup storage berizin. Jangan commit artefak.

## Restore terisolasi

1. Buat database restore yang jelas bukan development/staging aktif.
2. Verifikasi checksum, restore SQL dan private storage ke target sementara.
3. Jalankan `migrate:status`, application boot dan health check.
4. Bandingkan count kritis: migrations, users, people, patients, role/permission pivots dan entitas klinis.
5. Verifikasi relasi user-person, patient-person, audit trail dan akses file privat.
6. Hapus target/artefak rehearsal setelah bukti non-sensitif dicatat.

## Bukti Phase 5D lokal

Dump tanpa routines dan archive private storage lulus checksum. Restore ke `poskestren_phase5d_restore` menghasilkan count identik: migrations 57, roles 6, permissions 123, users 5, people 5, patients 1, medical visits 0; relasi user-person 5 dan patient-person 1. Ini bukan bukti restore staging/production.
