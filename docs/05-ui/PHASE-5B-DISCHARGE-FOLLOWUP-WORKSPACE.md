---
id: DOC-UI-P5B-DISCHARGE-FOLLOWUP-WORKSPACE
title: "Phase 5B Discharge, Follow-Up & Handoff Continuity Specification"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B Discharge, Follow-Up & Handoff Continuity Specification

Dokumen ini mendeskripsikan implementasi alur kepulangan klinis (*clinical discharge*), rencana tindak lanjut / kontrol berkala (*follow-up*), pembatasan aktivitas fisik (*activity restrictions*), dan lembar serah-terima operasional santri (*operational handoff*) yang aman privasi (*minimum necessary privacy*).

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
PRIVACY_RULE=Operational handoffs to dorm wardens must omit medical diagnoses and raw clinical SOAP logs.
```

---

## 1. Komponen Discharge Workspace (`discharges.workspace`)

### A. Evaluasi Kesiapan Pulang (Readiness Checklist)
Sebelum resume medis difinalisasi, evaluasi kesiapan otomatis ditampilkan:
- Pemeriksaan Tanda Vital Terakhir: tercatat & stabil.
- Pengkajian SOAP: berstatus `finalized`.
- Ruang Observasi: episode observasi telah berstatus `completed` (tidak ada episode aktif).
- Rujukan RS: tidak ada rujukan aktif yang sedang berjalan tanpa catatan kembali.
- Resep Obat: instruksi obat telah direkam secara jelas.

### B. Formulir Resume Kepulangan & Instruksi Aktivitas
- Tipe disposisi: `return_to_activity`, `rest_required`, `referred_again`, `transfer_of_care`, dll.
- Destinasi kepulangan: Asrama spesifik / Rumah orang tua.
- Kondisi akhir saat pulang (*stable / improved / resolved*).
- Rekomendasi aktivitas (*full_activity, limited_activity, rest, temporarily_not_cleared*).
- Catatan anjuran istirahat dan pantangan khusus.

### C. Rencana Tindak Lanjut / Follow-Up
- Checkbox penandaan kebutuhan kontrol ulang.
- Tanggal dan jam target follow-up (`follow_up_date`).
- Ringkasan rencana evaluasi klinis lanjutan.

---

## 2. Minimum Necessary Operational Handoffs

Untuk pembina asrama, wali santri, atau pengurus madrasah:
- Lembar serah-terima operasional hanya memuat instruksi yang relevan secara praktis:
  - Destinasi asrama dan tanggal kembali.
  - Anjuran istirahat (e.g. *"Istirahat di kamar 2 hari"*).
  - Pembatasan aktivitas fisik (e.g. *"Bebas dari kegiatan olahraga dan piket selama 3 hari"*).
  - Tanda bahaya/kondisi darurat yang mengharuskan santri segera dibawa kembali ke Poskestren.
- **Dilarang keras**: Memaparkan diagnosis penyakit klinis, rekam SOAP lengkap, atau detail anamnesis intim pada lembar operasional non-medis.
