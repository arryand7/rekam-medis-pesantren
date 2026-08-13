---
id: DOC-GRAPHIFY-BASELINE-REVIEW
title: "Graphify Baseline Review Report"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Laporan Tinjauan Baseline Knowledge Graph (Graphify)

Dokumen ini mencatat status dan instruksi penyiapan Knowledge Graph menggunakan skill Graphify pada repositori **SABIRA POSKESTREN Health**.

## 1. Status Eksekusi Graphify CLI

Saat perintah `graphify . --mode deep` dijalankan pada repositori saat ini, Graphify mendeteksi:
- **Dokumen Markdown (.md)**: 125 file (termasuk 106 file dokumentasi domain/persyaratan/arsitektur).
- **Source Code**: 0 file (karena Laravel skeleton belum di-bootstrap pada Phase 0 awal ini).

Graphify memerlukan API key (`GEMINI_API_KEY` atau `GOOGLE_API_KEY`) untuk melakukan ekstraksi tematik/semantik pada file dokumentasi non-kode dalam mode `--mode deep`. Karena variabel lingkungan API key belum dikonfigurasi pada environment terminal subprocess lokal, Graphify belum dapat menghasilkan visualisasi graph lengkap secara otomatis pada iterasi ini.

Sesuai aturan tata kelola baseline Phase 0, hasil graph **tidak boleh dikarang atau direkayasa**.

## 2. Exact Command yang Dibutuhkan

Untuk membangun Knowledge Graph secara penuh setelah API key tersedia atau setelah Laravel Foundation di-bootstrap:

```bash
# Set Gemini API Key (atau provider LLM yang didukung)
export GEMINI_API_KEY="<your-gemini-api-key>"

# Jalankan ekstraksi deep knowledge graph (kode + markdown)
graphify . --mode deep
```

Perintah tambahan untuk kueri graph:
```bash
# Kueri relasi Gate -> Person -> User -> Patient
graphify query "Bagaimana alur dan relasi antara Gate SSO, Person, User, dan Patient?"

# Kueri aturan konsultasi klinis dan rujukan
graphify query "Apa aturan bisnis untuk remote clinical consultation dan rujukan rumah sakit?"

# Kueri kelayakan pasien (patient eligibility)
graphify query "Siapa saja pengguna yang berhak menjadi pasien di POSKESTREN?"
```

## 3. Rencana Pembaruan Graphify Pasca-Bootstrap (Tahap D)

Setelah Tahap D (Laravel Foundation Bootstrap) selesai dan file source code PHP/Blade/CSS dibuat:
1. `graphify update .` atau `graphify . --mode deep` akan dijalankan untuk merekam seluruh komponen arsitektur fondasi dan pemetaan dokumentasi (`docs/12-graphify/DOCUMENT-CODE-MAPPING.md`).
2. Artefak graph (`graphify-out/graph.html`, `graphify-out/GRAPH_REPORT.md`, `graphify-out/graph.json`) akan diperbarui secara otomatis.
