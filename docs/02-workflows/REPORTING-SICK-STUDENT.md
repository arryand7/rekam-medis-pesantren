---
id: DOC-WF-REPORT
title: "Workflow Pelaporan Santri Sakit"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# WF-REPORT-001 — Pelaporan Santri Sakit

## Trigger
Santri mengeluh sakit atau ditemukan menunjukkan kondisi yang memerlukan pemeriksaan.

## Aktor
Santri, teman, pengasuh, guru, wali kelas, petugas piket, atau tim kesehatan.

## Alur
1. Pelapor mengidentifikasi santri.
2. Pelapor menyampaikan keluhan singkat dan lokasi.
3. Santri diarahkan atau diantar ke POSKESTREN.
4. Jika kondisi darurat, jalankan workflow emergency.
5. POSKESTREN mengonfirmasi penerimaan.

## Data
Pelapor, hubungan dengan santri, lokasi, waktu laporan, keluhan singkat, moda pengantaran.

## Acceptance
- Laporan tidak menggantikan registrasi kunjungan.
- Santri yang belum tiba tetap terlihat sebagai laporan belum selesai bila fitur ini diaktifkan.
- Data medis rinci tidak diminta dari pelapor non-medis.
