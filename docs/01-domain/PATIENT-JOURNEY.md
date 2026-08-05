---
id: DOC-PATIENT-JOURNEY
title: "Perjalanan Santri sebagai Pasien"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Perjalanan Person sebagai Pasien

```mermaid
flowchart TD
    A[Santri diketahui sakit] --> B[Pelaporan atau pengantaran]
    B --> C[Registrasi di POSKESTREN]
    C --> D[Pemeriksaan awal]
    D --> E[Assessment dan tindakan pertama]
    E --> F{Disposisi}
    F -->|Kembali| G[Instruksi dan pemulangan]
    F -->|Observasi| H[Observasi berkala]
    H --> I{Evaluasi}
    I -->|Membaik| G
    I -->|Perlu rujukan| J[Persiapan rujukan]
    F -->|Rujukan| J
    F -->|Darurat| K[Penanganan darurat]
    K --> J
    J --> L[Fasilitas kesehatan]
    L --> M[Kembali dan tindak lanjut]
    M --> G
```

## Titik data utama

- Siapa menemukan/melaporkan.
- Lokasi asal.
- Waktu tiba.
- Keluhan.
- Tanda vital.
- Assessment.
- Tindakan.
- Disposisi.
- Status observasi.
- Obat.
- Rujukan.
- Instruksi akhir.
- Pihak yang diberi informasi.

## Risiko perjalanan

- Santri tidak benar-benar tiba di POSKESTREN.
- Kunjungan dibuat terlambat.
- Pergantian shift tanpa handover.
- Observasi tanpa monitoring.
- Obat diberikan tanpa pencatatan.
- Rujukan tidak ditutup.
- Santri kembali ke asrama tanpa status yang jelas.

## Variasi berdasarkan tipe pasien

- Santri: wajib mengikuti aturan lokasi POSKESTREN dan handover ke asrama/sekolah.
- Guru/staf/pengasuh: dapat datang ke POSKESTREN dan memperoleh workflow klinis yang sama, tetapi tanpa aturan keberadaan asrama.
- Pengguna manusia lain: mengikuti kebijakan organisasi yang ditetapkan.

## Cabang konsultasi eksternal

Setelah assessment, petugas dapat membuat `ClinicalConsultation`:

```text
Assessment
  -> Consultation Summary
  -> Secure transmission
  -> External advice
  -> Local clinical decision
  -> Observation | Discharge | Referral
```
