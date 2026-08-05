---
id: DOC-PRIVACY
title: "Privasi Data Medis"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Privasi Data Medis

## Klasifikasi

- Sangat sensitif: diagnosis, assessment, kondisi khusus, dokumen rujukan.
- Sensitif: obat, alergi, vital, riwayat kunjungan.
- Operasional terbatas: berada di POSKESTREN, izin, kontrol.
- Agregat: statistik tanpa identitas.

## Prinsip

- Minimum necessary.
- Purpose limitation.
- Need-to-know.
- Segregation of duties.
- Audit access.
- Tidak menggunakan data nyata pada development.
- Tidak mengirim detail medis melalui notifikasi umum.
- Screenshot, export, dan print dibatasi.
- Cache browser untuk halaman sensitif diminimalkan.
- Data sensitif tidak muncul pada URL atau analytics pihak ketiga.

## Pemberian informasi

Setiap audience memiliki template informasi sendiri. Pengasuh dan wali kelas menerima status operasional, bukan seluruh catatan klinis.

## Konsultasi eksternal

- Gunakan data minimum.
- Hindari identifier tambahan yang tidak diperlukan.
- Catat recipient dan purpose.
- Dokumen consultation tidak ditempatkan pada public storage.
- Link download memiliki expiry dan audit.
- Respons eksternal diperlakukan sebagai data medis sensitif.
