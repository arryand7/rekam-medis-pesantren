---
id: DOC-UPDATE-SUMMARY
title: "Ringkasan Pembaruan Kebutuhan"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Ringkasan Pembaruan Kebutuhan

Paket versi 2 menambahkan tiga capability utama.

## 1. Person-centric medical record

Aplikasi tidak lagi memodelkan pasien hanya sebagai santri. Semua identitas manusia yang disinkronkan dari Gate dapat memiliki profil pasien dan riwayat kesehatan.

Pengecualian hanya untuk akun teknis atau akun administratif murni yang tidak merepresentasikan manusia. Permission `admin` bukan alasan otomatis untuk menghapus kelayakan pasien dari guru, staf, atau pengguna manusia lain.

## 2. Konsultasi klinis jarak jauh

Tim kesehatan dapat menyusun ringkasan keluhan, hasil assessment, tanda vital, tindakan awal, dan pertanyaan konsultasi untuk Puskesmas atau rumah sakit tanpa pasien harus langsung datang.

Capability ini adalah konsultasi profesional-ke-profesional dan tidak boleh:

- menunda rujukan pada kondisi darurat atau red flag;
- menyatakan diagnosis eksternal tanpa respons dari tenaga yang berwenang;
- mengirim data melalui kanal yang tidak disetujui;
- membagikan data melebihi kebutuhan konsultasi.

## 3. Sinkronisasi Gate yang aman

Gate menjadi sumber kebenaran identitas dan tipe pengguna. Aplikasi menyimpan proyeksi lokal yang dapat ditelusuri, idempotent, dan tidak menghapus riwayat kesehatan ketika akun dinonaktifkan.

## Dokumen baru

- `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md`
- `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `docs/07-security/GATE-SYNC-SECURITY.md`
- `docs/07-security/REMOTE-CONSULTATION-GOVERNANCE.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/12-graphify/GRAPHIFY-INSTALLATION.md`
- `docs/11-decisions/ADR-006-PERSON-PATIENT-SEPARATION.md`
- `docs/11-decisions/ADR-007-REMOTE-CLINICAL-CONSULTATION.md`
