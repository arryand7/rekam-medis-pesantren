---
id: DOC-USE-CASES
title: "Use Cases"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Use Cases

## UC-VISIT-001 — Membuat kunjungan

**Aktor:** petugas penerima  
**Prasyarat:** login dan memiliki permission.  
**Alur utama:** cari santri, cek kunjungan aktif, masukkan keluhan, simpan.  
**Postcondition:** kunjungan `waiting_assessment`, audit tercatat.  
**Alternatif:** override kunjungan aktif dengan alasan.

## UC-ASSESS-001 — Finalisasi assessment

**Aktor:** petugas berwenang  
**Prasyarat:** kunjungan aktif.  
**Alur:** isi riwayat, vital, temuan, assessment, tindakan, disposisi.  
**Postcondition:** assessment immutable melalui edit biasa.

## UC-OBS-001 — Memulai observasi

**Aktor:** petugas berwenang  
**Prasyarat:** disposisi observasi.  
**Postcondition:** episode observasi aktif.

## UC-MED-001 — Mencatat pemberian obat

**Aktor:** petugas berwenang  
**Prasyarat:** identitas, obat, alergi telah ditinjau.  
**Postcondition:** administrasi tercatat dan stok berubah atomik.

## UC-REF-001 — Membuat rujukan

**Aktor:** petugas berwenang  
**Prasyarat:** alasan rujukan tersedia.  
**Postcondition:** referral aktif dan kunjungan berubah state.

## UC-DIS-001 — Menutup kunjungan

**Aktor:** petugas berwenang  
**Prasyarat:** tidak ada observasi/rujukan aktif.  
**Postcondition:** instruksi final dan status operasional dibagikan sesuai aturan.

## UC-GATE-001 — Sinkronisasi pengguna

**Aktor:** super administrator  
**Alur:** dry run, review conflict, apply batch, reconciliation.  
**Postcondition:** person/user diperbarui tanpa perubahan clinical record.

## UC-PATIENT-001 — Membuat patient profile

**Aktor:** sistem atau petugas berwenang  
**Prasyarat:** person merepresentasikan manusia.  
**Postcondition:** patient ID lokal stabil dan dapat menerima health record.

## UC-CONSULT-001 — Meminta konsultasi eksternal

**Aktor:** petugas kesehatan berwenang  
**Prasyarat:** assessment tersedia dan emergency guard lulus.  
**Postcondition:** summary final, transmission audit, status tracked.

## UC-CONSULT-002 — Mencatat respons eksternal

**Aktor:** petugas berwenang atau integrasi terautentikasi  
**Postcondition:** advice teratribusi dan tidak otomatis menjadi local assessment.
