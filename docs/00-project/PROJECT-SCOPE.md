---
id: DOC-PROJECT-SCOPE
title: "Ruang Lingkup Proyek"
status: draft
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# Ruang Lingkup Proyek

## In scope

- Autentikasi dan authorization.
- Sinkronisasi identitas santri dan pengguna.
- Profil kesehatan dasar.
- Alergi dan kondisi medis penting.
- Registrasi kunjungan.
- Keluhan dan tanda vital.
- Assessment dan tindakan awal.
- Disposisi.
- Observasi.
- Pemberian obat.
- Stok obat dasar.
- Rujukan.
- Kembali dari rujukan.
- Pemulangan dari POSKESTREN.
- Status izin sakit operasional.
- Audit log.
- Dashboard dan laporan dasar.
- Theme light, dark, system.
- Dokumentasi dan knowledge graph.

## Out of scope fase awal

- Diagnosis otomatis berbasis AI.
- Rekomendasi dosis otomatis.
- Telemedicine.
- Klaim BPJS/asuransi otomatis.
- Rekam medis rumah sakit penuh.
- Billing rumah sakit.
- Mobile app native.
- Integrasi alat medis otomatis.
- Portal publik.
- Marketplace obat.
- Analitik prediktif.

## Batas sistem

Aplikasi mendukung dokumentasi dan workflow. Aplikasi tidak menggantikan keputusan klinis petugas yang berwenang.

## Asumsi awal

- Setiap santri memiliki identifier unik.
- POSKESTREN memiliki pengguna terautentikasi.
- Aplikasi dapat diakses dari jaringan sekolah.
- SOP resmi akan diberikan dan divalidasi sebelum fitur klinis dinyatakan final.

## Tambahan in scope

- Proyeksi detail pengguna dari Gate.
- Sinkronisasi tipe pengguna dan status aktif.
- Reconciliation report identitas.
- Profil pasien untuk semua pengguna manusia.
- Ringkasan konsultasi klinis jarak jauh.
- Pencatatan respons Puskesmas/rumah sakit.
- Lampiran data klinis minimum untuk konsultasi.
- Audit pengiriman dan penerimaan informasi.

## Tambahan out of scope

- Diagnosis otomatis oleh aplikasi.
- Konsultasi publik tanpa kerja sama dan kanal resmi.
- Pengiriman rekam medis penuh melalui pesan pribadi yang tidak disetujui.
- Menganggap saran informal tanpa identitas tenaga kesehatan sebagai diagnosis resmi.
