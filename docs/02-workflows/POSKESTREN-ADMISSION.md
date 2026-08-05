---
id: DOC-WF-ADMISSION
title: "Workflow Penerimaan POSKESTREN"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# WF-ADMISSION-001 — Penerimaan POSKESTREN

## Prasyarat
Santri telah tiba atau sedang ditangani langsung.

## Alur
1. Cari santri dari sumber identitas resmi.
2. Periksa kunjungan aktif.
3. Buat kunjungan.
4. Catat waktu tiba dari server.
5. Catat keluhan utama dan sumber laporan.
6. Tetapkan petugas penerima.
7. Tampilkan alergi/kondisi penting.
8. Ubah status menjadi `waiting_assessment`.

## Edge cases
- Identitas belum tersinkron.
- Kunjungan aktif ganda.
- Kondisi darurat.
- Koneksi integrasi identitas gagal.

## Audit
Pembuatan kunjungan, penggabungan duplikat, dan override harus diaudit.
