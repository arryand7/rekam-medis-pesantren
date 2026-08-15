---
id: DOC-PHASE-5D-MIGRATION-REHEARSAL
title: "Phase 5D Migration Rehearsal"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D Migration Rehearsal

Rehearsal dilakukan pada database lokal terisolasi `poskestren_phase5d_migration`; database development utama tidak dihapus atau dimigrasi ulang.

| Langkah | Hasil |
|---|---|
| Database kosong dan status awal | PASS — migration table belum ada sesuai ekspektasi |
| `migrate --force` | PASS — 57 migration |
| rerun `migrate --force` | PASS — tidak ada migration tertunda |
| `migrate:status` | PASS — seluruh migration Ran |
| seed opt-in demo | PASS |
| application boot | PASS |

Migration baru `2026_08_14_000600_create_model_has_permissions_table.php` menambahkan direct user permission pivot. Ia bersifat additive dan rollback-nya hanya menghapus pivot baru; deploy pertama tetap wajib diawali backup tervalidasi.

Database rehearsal dan restore lokal dihapus setelah bukti non-sensitif dicatat; keduanya bukan data staging/production.
