---
id: DELIVERY-PUBLIC-REPOSITORY-DATA-SANITIZATION
title: "Public Repository Data Sanitization"
status: complete
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Public Repository Data Sanitization

## Audit data

Tree dan history diperiksa untuk database/dump, CSV/spreadsheet, archive, screenshot/media, log, backup, export, email, nomor telepon, NIK/NIS/NIP/MRN, alamat, nama pasien, dan fixture mitra. Tidak ada database, dump, spreadsheet, PDF, screenshot, archive, atau private export yang tracked.

## Sanitasi yang diterapkan

- domain email test menggunakan `.test`; endpoint dan alamat integrasi contoh memakai `.invalid`;
- fixture Gate/fake client memakai identifier berawalan `TEST-`;
- mitra kesehatan demo memakai nama, alamat, kontak, nomor perjanjian, dan tenaga fiktif eksplisit;
- demo seed tetap opt-in melalui `SEED_DEMO_DATA=false`;
- path private storage, backup, export, database, dan archive ditolak `.gitignore`;
- dokumentasi menegaskan bahwa screenshot lokal dan data rehearsal bukan bukti produksi.

Nama generik yang tersisa pada factory/test adalah data sintetis yang dibuat per-test dan tidak merepresentasikan pasien nyata. Identifier UUID/ULID pada dokumentasi visual lama berasal dari database rehearsal sintetis, bukan export operasional.

## Aturan contributor

Gunakan Faker atau label `Contoh/Fiktif/Synthetic`, domain reserved, dan nomor non-operasional. Jangan mengambil screenshot environment nyata tanpa redaksi. Jangan menaruh data medis atau identitas personal di commit, PR, issue, fixture, log, atau Graphify input.

Hasil: `NO-CONFIRMED-REAL-PATIENT-DATA-IN-REPOSITORY`.
