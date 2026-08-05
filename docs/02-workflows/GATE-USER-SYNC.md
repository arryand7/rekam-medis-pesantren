---
id: DOC-WF-GATE-SYNC
title: "Workflow Sinkronisasi Pengguna dari Gate"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# WF-GATE-SYNC-001 — Sinkronisasi Pengguna

## Tujuan

Menyamakan detail pengguna lokal dengan Gate tanpa menduplikasi person atau merusak rekam medis.

## Trigger

- Sinkronisasi manual oleh super admin.
- Scheduled reconciliation.
- Event/webhook Gate pada fase lanjutan.
- First login.

## Alur

1. Autentikasi aplikasi ke Gate.
2. Ambil page pengguna beserta source version/timestamp.
3. Validasi schema dan signature/token.
4. Cari berdasarkan `gate_user_id`.
5. Jika tidak ada, buat/update `person` dan `user`.
6. Tentukan apakah record merepresentasikan manusia.
7. Buat patient profile bila eligible sesuai strategi.
8. Sinkronkan tipe pengguna dan atribut organisasi.
9. Jangan mengubah field klinis.
10. Tandai user tidak ditemukan sebagai `source_missing`; jangan langsung menghapus.
11. Buat reconciliation report.
12. Simpan audit dan cursor sinkronisasi.

## Matching legacy

Urutan matching:

1. `gate_user_id`;
2. mapping table yang telah disetujui;
3. candidate match NIS/NIP/email untuk review manual;
4. jangan auto-merge berdasarkan nama saja.

## Conflict classes

- duplicate external ID;
- identifier collision;
- local record without Gate;
- Gate user without local;
- stale local data;
- user type mismatch;
- invalid or unsupported type;
- source deactivated.

## Safety

- Idempotency.
- Pagination.
- Retry.
- Rate limit.
- Transaction per record/batch yang aman.
- Dry run dan preview.
- No hard delete.
- Audit before/after.
