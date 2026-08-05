---
id: DOC-MEDICAL-VERSIONING
title: "Versioning Rekam Medis"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Versioning Rekam Medis

## Tujuan

Mempertahankan catatan asli dan jejak koreksi.

## Status catatan

- `draft`: dapat diedit pemilik/role tertentu.
- `finalized`: tidak dapat diedit langsung.
- `amended`: memiliki addendum.
- `voided`: dinyatakan tidak berlaku dengan alasan, tetapi tetap tersimpan.

## Addendum

Addendum menyimpan:
- record asal,
- isi tambahan/koreksi,
- alasan,
- actor,
- waktu,
- approval bila diperlukan.

## Larangan

- Mengubah `updated_at` dan isi final tanpa history.
- Menghapus baris final untuk menyembunyikan kesalahan.
- Mengganti actor.
- Backdate tanpa mencatat waktu input aktual.

## Concurrency

Gunakan `lock_version` atau mekanisme optimistic locking pada form medis penting.
