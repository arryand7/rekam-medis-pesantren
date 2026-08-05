---
id: DOC-WF-REMOTE-CONSULT
title: "Workflow Konsultasi Klinis Jarak Jauh"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# WF-CONSULT-001 — Konsultasi Klinis Jarak Jauh

## Tujuan

Memberikan data acuan yang terstruktur kepada Puskesmas atau rumah sakit agar tenaga kesehatan eksternal dapat memberikan pertimbangan awal tanpa pasien langsung datang.

## Prasyarat

- Patient dan visit valid.
- Assessment lokal telah dibuat.
- Petugas memiliki permission.
- Red flag telah dievaluasi.
- Mitra dan kanal komunikasi disetujui.
- Dasar persetujuan/otorisasi tersedia sesuai kebijakan.

## Alur

1. Petugas memilih visit.
2. Sistem menyusun draft ringkasan dari data yang dipilih:
   - identitas minimum;
   - keluhan utama dan kronologi;
   - tanda vital;
   - alergi dan kondisi penting;
   - temuan;
   - tindakan dan obat yang telah diberikan;
   - pertanyaan konsultasi;
   - lampiran yang relevan.
3. Petugas meninjau dan menghapus data yang tidak diperlukan.
4. Petugas memilih fasilitas dan penerima.
5. Sistem memeriksa red flag dan status emergency.
6. Petugas memfinalisasi ringkasan.
7. Sistem mengirim melalui kanal resmi atau menghasilkan dokumen aman.
8. Status menjadi `sent`.
9. Respons eksternal dicatat dengan atribusi.
10. Petugas lokal menilai respons dan membuat keputusan lokal.
11. Kunjungan berlanjut ke observasi, discharge, atau rujukan.
12. Consultation ditutup.

## State

`draft -> ready -> sent -> acknowledged -> responded -> completed`

Alternatif: `cancelled`, `expired`, `superseded`.

## Guard

- Consultation tidak dapat menjadi pengganti emergency referral.
- Respons tanpa identitas sumber ditandai `unverified`.
- Diagnosis eksternal tidak ditulis sebagai diagnosis lokal tanpa keputusan petugas lokal.
- Revisi setelah dikirim menghasilkan versi baru.
- Pengiriman, download, dan respons diaudit.

## Data minimum respons

- fasilitas;
- nama tenaga kesehatan;
- profesi/nomor identifikasi bila tersedia;
- waktu;
- kanal;
- saran;
- keterbatasan;
- rekomendasi tindak lanjut;
- kebutuhan rujukan langsung.

## [PERLU DIKONFIRMASI]

- Mitra resmi.
- Kanal resmi.
- Template persetujuan.
- SLA respons.
- Apakah komunikasi sinkron atau asinkron.
- Format dokumen dan tanda tangan.
