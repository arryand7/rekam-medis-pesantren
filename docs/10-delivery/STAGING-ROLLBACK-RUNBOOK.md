---
id: DOC-STAGING-ROLLBACK-RUNBOOK
title: "Staging Rollback Runbook"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Staging Rollback Runbook

## Pemicu

Rollback bila health/readiness gagal, migration tidak selesai, login/authorization rusak, data privat terekspos, outbox mengirim payload salah, atau integrity check gagal.

## Urutan

1. Hentikan traffic/aktifkan maintenance; nonaktifkan Gate, Attendance, scheduler dan worker yang terkait.
2. Catat waktu server, gejala, commit dan migration terakhir; jangan log data klinis.
3. Bila perubahan hanya kode dan schema tetap backward-compatible, arahkan symlink ke release sebelumnya lalu restart worker.
4. Bila schema/data telah berubah dan tidak kompatibel, pulihkan database serta private storage dari backup pra-deploy ke target terisolasi terlebih dahulu, verifikasi checksum/count/relasi, kemudian lakukan controlled restore.
5. Jangan menggunakan `migrate:fresh`, `db:wipe`, hard delete data medis atau rollback migration tanpa review dampak data.
6. Ulangi health, login, policy dan integrity check sebelum membuka traffic.
7. Simpan incident record dan tindakan korektif; credential yang mungkin terekspos harus dirotasi.

Keputusan memilih code rollback versus data restore harus dibuat oleh release owner dan database owner.
