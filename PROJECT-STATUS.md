---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Status Proyek

## Fase saat ini

**Phase 2A Completed — Patient Health Profile & Medical Visit Intake Foundation**

## Perubahan & Fitur Selesai di Phase 2A

- [x] **Phase 1 Closure Audit**: Laporan closure Phase 1 diterbitkan di [PHASE-1-CLOSURE.md](file:///Users/ryand/Documents/LARAVEL/sabira/rekam-medis-ponpes/docs/10-delivery/PHASE-1-CLOSURE.md) dengan status `PASSED`.
- [x] **Refaktor Boundary Pasien vs Profil Kesehatan**:
  - `patients` murni menyimpan identitas pasien lokal & eligibility.
  - Skema ULID `patient_health_profiles` (`blood_type`, `emergency_notes`).
- [x] **Alergi Terstruktur & Non-Destructive Audit**:
  - Skema `patient_allergies` (`allergen`, `reaction`, `severity`, `status` [`suspected`, `confirmed`, `resolved`, `entered-in-error`]).
  - Koreksi kesalahan pendaftaran alergi menggunakan status `entered-in-error` tanpa hard delete.
- [x] **Kondisi Medis & Kontak Darurat**:
  - Skema `patient_medical_conditions` (`condition_name`, `status`, `notes`).
  - Skema `patient_emergency_contacts` (`name`, `relationship`, `phone`, `priority`, `source`).
- [x] **Registrasi Kunjungan Medis (Medical Visit Intake)**:
  - Skema `medical_visits` (`visit_number` `VIS-YYYYMMDD-XXXXX`, `status` [`registered`, `waiting_assessment`, `cancelled`], `arrived_at` dari server, `chief_complaint`, `reporting_type`).
  - **Active Visit Guard**: Transaksi aman mencegah 2 kunjungan aktif ganda untuk pasien yang sama kecuali di-override dengan izin eksplisit dan alasan yang diaudit.
  - **Pembatalan Non-Destruktif**: Update status `cancelled` dengan alasan pembatalan wajib dan pencatatan log audit.
- [x] **Otorisasi Server-Side**:
  - Policy terpasang: `PatientHealthProfilePolicy`, `PatientAllergyPolicy`, `MedicalVisitPolicy`.
- [x] **UI Management Phase 2A**:
  - Profil Rekam Medis Pasien (`/patients/{id}`), Antrean Intake Kunjungan (`/visits`), Form Registrasi Intake (`/visits/create`), Detail Kunjungan & Modal Pembatalan (`/visits/{id}`).

## Kemajuan Phase

- [x] **Phase 0 — Readiness & Foundation**: Selesai.
- [x] **Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync**: Selesai.
- [x] **Phase 2A — Patient Health Profile & Medical Visit Intake Foundation**: Selesai.
- [ ] **Phase 2B — Examination, Vital Signs, Assessment & Clinical Workflows**: Menunggu persetujuan pengguna.

## Last verified

- Tanggal: 2026-08-05
- Test Suite: 17 tests, 59 assertions (100% Passed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Route List: 27 routes terdaftar bersih
