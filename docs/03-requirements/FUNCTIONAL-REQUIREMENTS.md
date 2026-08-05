---
id: DOC-FUNCTIONAL-REQ
title: "Kebutuhan Fungsional"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Kebutuhan Fungsional

## Authentication dan identitas

- **FR-AUTH-001** Pengguna dapat login melalui mekanisme lokal development dan SSO production.
- **FR-AUTH-002** Sistem menentukan role dan permission dari server.
- **FR-ID-001** Sistem menyinkronkan santri dari sumber resmi.
- **FR-ID-002** Sistem menampilkan konflik sinkronisasi tanpa menimpa data secara diam-diam.

## Profil kesehatan

- **FR-PROFILE-001** Petugas berwenang dapat melihat profil kesehatan.
- **FR-PROFILE-002** Sistem menyimpan alergi, kondisi penting, obat rutin, dan kontak darurat.
- **FR-PROFILE-003** Perubahan profil kesehatan diaudit.

## Kunjungan

- **FR-VISIT-001** Petugas dapat membuat kunjungan.
- **FR-VISIT-002** Sistem mencegah kunjungan aktif ganda tanpa override.
- **FR-VISIT-003** Sistem mencatat keluhan utama.
- **FR-VISIT-004** Sistem mengelola lifecycle kunjungan.
- **FR-VISIT-005** Sistem menampilkan timeline kunjungan.

## Pemeriksaan

- **FR-VITAL-001** Petugas dapat mencatat tanda vital berulang.
- **FR-ASSESS-001** Petugas berwenang dapat membuat dan memfinalisasi assessment.
- **FR-ACTION-001** Petugas dapat mencatat tindakan awal.
- **FR-DISPOSITION-001** Assessment menghasilkan disposisi.

## Observasi

- **FR-OBS-001** Petugas dapat memulai observasi.
- **FR-OBS-002** Petugas dapat mencatat monitoring berkala.
- **FR-OBS-003** Sistem mendukung handover.
- **FR-OBS-004** Observasi ditutup dengan outcome.

## Obat

- **FR-MED-001** Admin dapat mengelola master obat.
- **FR-MED-002** Petugas farmasi dapat mengelola stok/batch.
- **FR-MED-003** Petugas berwenang dapat mencatat pemberian obat.
- **FR-MED-004** Sistem memperingatkan alergi.
- **FR-MED-005** Sistem mengelola pembatalan/penolakan tanpa menghapus jejak.

## Rujukan

- **FR-REF-001** Petugas berwenang dapat membuat rujukan.
- **FR-REF-002** Sistem menyimpan tujuan, urgensi, pendamping, transportasi, dan komunikasi.
- **FR-REF-003** Sistem melacak status rujukan.
- **FR-REF-004** Sistem mencatat hasil kembali dari rujukan.

## Discharge dan informasi

- **FR-DIS-001** Petugas dapat menutup kunjungan dengan instruksi.
- **FR-DIS-002** Sistem dapat menghasilkan status izin/pembatasan aktivitas.
- **FR-NOTIFY-001** Sistem mengirim notifikasi sesuai aturan dan permission.

## Laporan

- **FR-REPORT-001** Dashboard menampilkan kunjungan aktif.
- **FR-REPORT-002** Laporan agregat berdasarkan periode, kelas, asrama, keluhan, dan outcome.
- **FR-REPORT-003** Ekspor mengikuti authorization dan audit.

## Admin dan audit

- **FR-ADMIN-001** Admin mengelola role/permission.
- **FR-AUDIT-001** Sistem mencatat mutasi medis.
- **FR-AUDIT-002** Auditor berwenang dapat menelusuri audit tanpa mengubahnya.
- **FR-THEME-001** Pengguna dapat memilih light, dark, atau system.

## Gate sync dan person/patient

- **FR-GATE-001** Sistem mengambil detail pengguna dari Gate dengan pagination dan authentication.
- **FR-GATE-002** Sistem melakukan upsert berdasarkan `gate_user_id`.
- **FR-GATE-003** Sistem menyinkronkan tipe pengguna dan status tanpa mengubah rekam medis.
- **FR-GATE-004** Sistem menyediakan dry-run dan reconciliation report.
- **FR-GATE-005** Sistem mempertahankan patient history saat user dinonaktifkan.
- **FR-PERSON-001** Sistem memisahkan person, user, role, dan patient.
- **FR-PATIENT-001** Semua person manusia eligible memiliki patient profile.
- **FR-PATIENT-002** Sistem mengecualikan account non-human dari patient creation.
- **FR-PATIENT-003** Role admin tidak mengubah patient eligibility.

## Konsultasi klinis jarak jauh

- **FR-CONSULT-001** Petugas dapat membuat draft clinical consultation dari visit.
- **FR-CONSULT-002** Petugas dapat memilih data minimum yang dibagikan.
- **FR-CONSULT-003** Sistem dapat menghasilkan ringkasan terstruktur dan versi PDF aman.
- **FR-CONSULT-004** Sistem melacak status pengiriman dan penerimaan.
- **FR-CONSULT-005** Sistem mencatat external clinical advice dengan atribusi.
- **FR-CONSULT-006** Petugas lokal mencatat keputusan setelah konsultasi.
- **FR-CONSULT-007** Sistem mencegah consultation menunda emergency referral.
- **FR-CONSULT-008** Sistem mengelola versioning dan audit consultation.
