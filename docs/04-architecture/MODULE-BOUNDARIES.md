---
id: DOC-MODULE-BOUNDARIES
title: "Batas Modul"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Batas Modul

## Identity
User, role, permission, profile, Gate mapping.

## Students
Identitas santri hasil sinkronisasi dan relasi kelas/asrama.

## HealthProfiles
Alergi, kondisi medis, obat rutin, kontak darurat.

## Visits
Registrasi, keluhan, lifecycle, timeline.

## ClinicalAssessments
Vital signs, assessment, diagnosis, tindakan.

## Observations
Episode observasi, monitoring, handover, bed.

## Pharmacy
Obat, batch, stok, movement.

## Medication
Order dan administration.

## Referrals
Rujukan, dokumen, status, hasil, tindak lanjut.

## Discharge
Instruksi akhir, pembatasan aktivitas, kontrol.

## Notifications
Notifikasi internal dan eksternal.

## Reporting
Dashboard dan laporan agregat.

## Audit
Audit trail medis dan administratif.

## Integrations
Gate, SSS, Absensi, dan channel komunikasi.

## Aturan dependency

- Modul tidak membaca tabel modul lain secara bebas.
- Gunakan service contract, query service, atau event.
- Reporting boleh memiliki read model.
- Audit menerima event dari semua modul.

## People
Proyeksi identitas manusia dari Gate.

## Patients
Patient identifier dan hubungan ke medical history.

## ClinicalConsultations
Summary, version, transmission, external advice, dan local decision.

## Pemisahan penting

`Identity/User` mengatur authentication. `People/Patients` mengatur siapa subjek rekam medis. Permission admin tidak boleh digunakan untuk menentukan patient eligibility.
