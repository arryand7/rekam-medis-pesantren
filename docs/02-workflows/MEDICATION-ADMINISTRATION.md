---
id: DOC-WF-MEDICATION
title: "Workflow Pemberian Obat"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# WF-MED-001 — Pemberian Obat

## Alur
1. Identifikasi santri dan kunjungan.
2. Tinjau alergi dan kontraindikasi yang tercatat.
3. Pilih order obat atau buat instruksi sesuai kewenangan.
4. Konfirmasi obat, dosis, satuan, rute, dan waktu.
5. Konfirmasi batch/stok bila digunakan.
6. Catat pemberian oleh petugas.
7. Kurangi stok secara atomik.
8. Catat reaksi atau penolakan bila ada.

## Status administrasi
`scheduled`, `administered`, `held`, `refused`, `missed`, `cancelled`.

## Safety
Aplikasi tidak menghitung dosis klinis secara otomatis pada MVP.
