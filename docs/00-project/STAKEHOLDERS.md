---
id: DOC-STAKEHOLDERS
title: "Stakeholder"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Stakeholder

| Stakeholder | Kepentingan | Keterlibatan |
|---|---|---|
| Kepala POSKESTREN | Keselamatan, SOP, kualitas layanan | Product owner domain |
| Tim kesehatan | Workflow cepat dan akurat | Pengguna utama dan validator |
| Pengasuh asrama | Mengetahui status operasional santri | Pengguna terbatas |
| Wali kelas | Mengetahui dampak kehadiran/kegiatan | Pengguna terbatas |
| Manajemen | Laporan agregat dan risiko | Sponsor dan reviewer |
| Tim IT | Keamanan, operasional, integrasi | Pemilik teknis |
| Developer | Implementasi sesuai requirement | Pelaksana |
| Santri | Mendapat pelayanan dan privasi | Subjek data |
| Wali santri | Mendapat informasi yang diizinkan | Stakeholder eksternal |
| Fasilitas rujukan | Menerima informasi rujukan | Pihak eksternal |
| Petugas farmasi | Stok dan pemberian obat | Pengguna modul obat |

## RACI awal

- Domain medis: Kepala POSKESTREN bertanggung jawab dan menyetujui.
- Arsitektur: Kepala IT bertanggung jawab.
- Keamanan: Kepala IT dan manajemen menyetujui.
- Workflow pengasuh: Koordinator asrama dikonsultasikan.
- Informasi wali: Manajemen dan POSKESTREN menyetujui.
- Implementasi: Tim developer bertanggung jawab.

| Administrator Gate | Menjamin kontrak identitas, tipe pengguna, status, dan hak akses | Pemilik sumber identitas |
| Puskesmas mitra | Memberikan pertimbangan atau menerima rujukan | Mitra klinis eksternal |
| Rumah sakit mitra | Memberikan pertimbangan atau menerima rujukan | Mitra klinis eksternal |
| Tenaga kesehatan eksternal | Memberikan respons teratribusi | Konsultan eksternal |

## Tambahan RACI

- Kontrak sinkronisasi Gate: Tim Gate accountable, Tim POSKESTREN consulted.
- Kelayakan pasien: Manajemen dan POSKESTREN accountable.
- Kanal konsultasi eksternal: POSKESTREN dan mitra kesehatan accountable.
- Data minimum konsultasi: Penanggung jawab medis accountable, IT responsible untuk enforcement.
