---
id: DOC-UI-P5B-OBSERVATION-WORKSPACE
title: "Phase 5B Observation Workspace Specification"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B Observation Workspace Specification

Dokumen ini mendeskripsikan implementasi workspace ruang observasi rawat inap Poskestren, komponen navigasi terpadu, pemantauan berkala, handover shift, dan penyelesaian episode.

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
COMPONENT_SET=PatientContextHeader, VisitStageNav, ObservationMonitoringForm, ShiftHandoverModal, CompleteObservationModal
```

---

## 1. Komponen & Visual Architecture

### A. Context Header & Stage Nav
Setiap layar observasi (`observations.show`) secara terpadu membungkus:
- `<x-patient-context-header :patient="$patient" :visit="$visit" />`
  - Nama santri, nomor rekam medis (MRN), badge kelayakan, jenis kelamin, NIS/NIP, badge alergi aktif dengan highlight merah jika ada alergi berat.
- `<x-visit-stage-nav :visit="$visit" current="observations" />`
  - 5-stage navigation bar (Overview, Vital & SOAP, Observasi, Obat & Farmasi, Kepulangan).

### B. Header Banner Episode Observasi
- Menampilkan lokasi kamar/ruang (e.g. `Ruang Observasi Putra`), label bed (e.g. `Bed 02`), dan petugas penanggung jawab jaga.
- Interval pemantauan berkala (e.g. `Tiap 60 Menit`).
- Badge status real-time (`active`, `completed`, `cancelled`).
- Tombol aksi: `Handover Shift` dan `Selesaikan Observasi`.

### C. Lembar Pemantauan Berkala (Periodic Monitoring Logs)
- Form input pencatatan ringkasan kondisi, perubahan gejala, dan klasifikasi kondisi umum (`good`, `moderate`, `weak`, `critical`).
- Timeline riwayat pemantauan tersusun kronologis mundur (`latest first`) dengan timestamp resmi server dan nama petugas pencatat.

### D. Penutupan Episode & Lock State
- Ketika episode berstatus `completed`, sistem secara otomatis mengunci workspace menjadi **Read-Only**.
- Form mutasi baru disembunyikan dan digantikan oleh banner informatif hijau: *"Observasi Telah Ditutup — Santri telah menyelesaikan masa observasi poskestren"*.

---

## 2. Rute & Controller Mapping

- `GET /observations` &rarr; Direktori episode observasi aktif & riwayat.
- `GET /observations/{id}` &rarr; Workspace lembar observasi santri.
- `POST /observations/{id}/monitoring` &rarr; Simpan pemantauan berkala baru.
- `POST /observations/{id}/handover` &rarr; Delegasi serah-terima shift antar petugas jaga.
- `POST /observations/{id}/complete` &rarr; Finalisasi episode observasi dengan penetapan outcome.
