---
id: DOC-BUSINESS-SCENARIOS
title: "Skenario Bisnis"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Skenario Bisnis

## TEST-BIZ-001 — Keluhan ringan
Santri datang, diperiksa, diberi instruksi, kembali dengan status jelas.

## TEST-BIZ-002 — Observasi
Santri menjalani observasi, monitoring berkala, lalu discharge.

## TEST-BIZ-003 — Memburuk saat observasi
Observasi berubah menjadi rujukan tanpa kehilangan timeline.

## TEST-BIZ-004 — Rujukan darurat
Data minimum dibuat cepat, kemudian dilengkapi retrospektif.

## TEST-BIZ-005 — Alergi
Obat dipilih, warning muncul, pemberian memerlukan keputusan berwenang.

## TEST-BIZ-006 — Stok bersamaan
Dua petugas tidak dapat mengurangi stok melebihi jumlah tersedia.

## TEST-BIZ-007 — Pergantian shift
Petugas berikutnya menerima handover aktif.

## TEST-BIZ-008 — Kunjungan ganda
Sistem menolak duplicate active visit tanpa override.

## TEST-BIZ-009 — Unauthorized access
Wali kelas tidak dapat membaca assessment melalui URL langsung.

## TEST-BIZ-010 — Koreksi catatan
Final record dikoreksi dengan addendum, catatan asli tetap ada.

## TEST-BIZ-011 — Guru menjadi pasien
Guru dengan role admin memiliki patient profile dan menerima visit tanpa perubahan permission.

## TEST-BIZ-012 — Gate deactivation
User dinonaktifkan; login ditolak tetapi rekam medis tetap utuh.

## TEST-BIZ-013 — Sync idempotent
Batch Gate yang sama dijalankan ulang tanpa duplicate.

## TEST-BIZ-014 — Identity conflict
Dua payload memakai identifier sama; sistem membuat conflict report dan tidak auto-merge.

## TEST-BIZ-015 — Remote consultation
Assessment disusun menjadi summary, dikirim, respons eksternal dicatat, dan keputusan lokal dibuat.

## TEST-BIZ-016 — Emergency guard
Red flag membuat referral dapat berjalan segera dan consultation tidak menahan workflow.
