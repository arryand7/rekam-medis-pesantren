---
id: DOC-SEC-OPERATIONAL-DATA-SHARING
title: "Panduan Pembagian Data Operasional & Standar Minimum Necessary"
status: active
last_updated: 2026-08-09
owner: "Tim Pengembang POSKESTREN"
---

# Panduan Pembagian Data Operasional & Standar Minimum Necessary

## 1. Prinsip Kerahasiaan Medis Pesantren

Sesuai undang-undang kesehatan dan standar privasi rekam medis, data klinis santri dan warga pesantren adalah rahasia medis yang dilindungi. Pihak operasional non-medis (Pembina Asrama, Wali Kelas/Guru, dan Pengurus Sekolah) hanya berhak menerima instruksi perawatan praktis yang diperlukan untuk menjalankan peran pengasuhan atau pendidikan (*Minimum Necessary Standard*).

## 2. Matriks Profil Payload Operasional

| Target Penerima | Field yang Diperbolehkan | Field yang Dilarang Keras |
|---|---|---|
| **Pembina Asrama (Dormitory Supervisor)** | Nama santri, identitas Gate, rekomendasi istirahat, petunjuk praktis asrama, jadwal kontrol ulang, eskalasi darurat. | Diagnosis medis, kode ICD-10, narasi pemeriksaan dokter, riwayat obat, catatan internal medis. |
| **Wali Kelas / Guru (Homeroom Teacher)** | Nama santri, identitas Gate, status partisipasi KBM/olahraga, akomodasi kehadiran sekolah. | Diagnosis medis, gejala klinis, resep obat, riwayat penyakit dahulu. |
| **SABIRA Absensi (Attendance System)** | Gate User ID, tipe disposisi (`rest`, `limited_activity`, `return_to_activity`), masa berlaku, cakupan aktivitas. | Semua catatan klinis, riwayat medis, nama keluhan pasien. |
| **Manajemen Eksekutif (Dashboard / Reports)** | Angka agregat numerik (jumlah kunjungan, utilisasi bed observasi, rasio rujukan, status stok farmasi). | Data medis level individu, daftar pasien per diagnosis. |

## 3. Penegakan Teknis di Tingkat Aplikasi

1. **Immutable DTO Runtime Guard**: `AttendanceHealthDispositionDTO` memvalidasi ketiadaan *forbidden keys* saat instansiasi.
2. **Payload Builders**: `AttendanceDispositionPayloadBuilder` memisahkan secara ketat pembuatan payload tiap peran penerima.
3. **Audit Trail**: Setiap pengaksesan laporan atau pengiriman event integrasi tercatat pada `audit_logs`.
