---
id: DOC-UI-P5B-CONSULTATION-WORKSPACE
title: "Phase 5B External Consultation Workspace Specification"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B External Consultation Workspace Specification

Dokumen ini mendeskripsikan implementasi workspace tele-konsultasi klinis jarak jauh dengan dokter mitra eksternal Poskestren, pembatasan hukum advice eksternal, dan penegasan keputusan klinis lokal.

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
TRANSPORT_MODE=SIMULATED TRANSPORT (LOCAL-DEVELOPMENT)
PRINCIPLE=External Clinical Advice != Local Clinical Order
```

---

## 1. Prinsip Klinis & Legalitas Konsultasi Jarak Jauh

1. **Pemisahan Advice Eksternal & Keputusan Lokal**:
   - Saran medis dari dokter luar disimpan dalam entitas `external_clinical_advices`.
   - Advice tersebut tidak dieksekusi secara otomatis oleh sistem.
   - Dokter Poskestren wajib menelaah advice tersebut dan menerbitkan `consultation_local_decisions`.
2. **Advisory Warning Banner**:
   - Menampilkan notice kepatuhan klinis resmi:
     > *"Saran klinis dari faskes mitra berstatus advisory/rekomendasi. Keputusan akhir penanganan dan instruksi medis santri sepenuhnya berada di bawah kewenangan dokter/petugas Poskestren berizin."*
3. **Simulated Transport Banner**:
   - Menandai kanal pengiriman sebagai `LOCAL-DEVELOPMENT / SIMULATED TRANSPORT` untuk integritas audit lingkungan lokal.

---

## 2. Layout & Komponen Workspace (`consultations.show`)

1. **Context & Navigation**:
   - `<x-patient-context-header :patient="$patient" :visit="$visit" />`
   - `<x-visit-stage-nav :visit="$visit" current="overview" />`
2. **Left Column (Snapshot & Payload)**:
   - **Pertanyaan Klinis Profesional**: Teks verbatim pertanyaan spesifik yang diajukan ke faskes mitra.
   - **Versioned Summary Snapshot**: Payload JSON rekam medis ringkas (nama, MRN, keluhan, diagnosis kerja, riwayat alergi) yang dikirim ke faskes mitra beserta SHA-256 checksum integrity.
   - **Riwayat Transmisi & Pengiriman**: Log pengiriman transmisi dengan channel (`whatsapp_simulated`, `direct_call`, `email`) dan timestamp resmi.
3. **Right Column (Advice Eksternal & Keputusan Medis)**:
   - **Kartu Saran Klinis Diterima**: Menampilkan nama dokter mitra, spesialisasi, isi anjuran klinis, dan timestamp penerimaan.
   - **Form Pencatatan Advice Eksternal**: Formulir input jika respon diterima via telepon/kanal lain.
   - **Penetapan Keputusan Klinis Lokal**: Formulir finalisasi yang mewajibkan dokter Poskestren memilih tipe keputusan (`continue_observation`, `continue_current_care`, `rest_recommended`, `referral_recommended`, `emergency_referral_required`) dan menuliskan pertimbangan medis (*rationale*).
