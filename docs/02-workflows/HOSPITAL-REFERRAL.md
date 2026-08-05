---
id: DOC-WF-REFERRAL
title: "Workflow Rujukan Rumah Sakit"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# WF-REF-001 — Rujukan

## Alur
1. Petugas berwenang menetapkan rujukan.
2. Catat alasan dan urgensi.
3. Pilih fasilitas tujuan.
4. Catat pendamping dan transportasi.
5. Tentukan pihak yang harus diinformasikan.
6. Buat ringkasan rujukan.
7. Catat waktu berangkat.
8. Pantau status sampai hasil tersedia.

## Data minimum
Tujuan, alasan, urgensi, waktu, pendamping, transportasi, petugas pembuat, status komunikasi.

## Darurat
Data yang tidak tersedia dapat dilengkapi setelah keberangkatan, tetapi harus diberi penanda incomplete emergency record.

## Hubungan dengan konsultasi jarak jauh

Konsultasi dapat mendahului rujukan pada kasus yang sesuai. Namun referral workflow harus dapat dimulai kapan saja tanpa menunggu consultation selesai. Consultation yang belum selesai dapat ditandai `superseded_by_referral`.
