---
id: DOC-CHANGELOG
title: "Changelog"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Changelog

Semua perubahan penting proyek dicatat di file ini.

## [Unreleased]

## [0.6.0] — 2026-08-05

### Added

- **Penyempurnaan Status Alergi**: Memisahkan `clinical_status` dan `verification_status` pada `patient_allergies`.
- **Modul Tanda Vital Terstruktur**: Tabel `vital_signs` dan `VitalSignService` dengan validasi rentang fisiologis dan penguncian finalization.
- **Modul Pengkajian Klinis Medis**: Tabel `clinical_assessments` dan `ClinicalAssessmentService` untuk anamnesis, pemeriksaan fisik, impresi diagnostik, dan addendum/revisi.
- **Tindakan Awal Murni Non-Obat**: Tabel `clinical_actions` untuk P3K, perawatan luka, hidrasi, dan tirah baring tanpa pemberian obat.
- **Rekomendasi Disposisi & Visit State Machine**: Penetapan rekomendasi disposisi dan transisi siklus hidup kunjungan medis ke `assessment_completed`.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk tanda vital, assessment klinis, dan tindakan awal (`VitalSignPolicy`, `ClinicalAssessmentPolicy`, `ClinicalActionPolicy`).
- **Clinical Assessment Workspace UI**: Antarmuka kerja pengkajian medis lengkap (`/visits/{id}/assessment`).
- **Feature Tests**: Pengujian Pest untuk `VitalSignTest`, `ClinicalAssessmentTest`, dan `ClinicalActionTest` (22 tests, 73 assertions passed).


### Added

- **Pemisahan Boundary Pasien**: Memindahkan data kesehatan dari tabel `patients` ke `patient_health_profiles` terpisah.
- **Alergi Terstruktur**: Implementasi `patient_allergies` terstruktur dengan penandaan status `entered-in-error` tanpa hard delete.
- **Kondisi Medis & Kontak Darurat**: Tabel dan relasi `patient_medical_conditions` serta `patient_emergency_contacts`.
- **Intake Kunjungan Medis**: Implementasi registrasi kunjungan medis `medical_visits` dengan nomor kunjungan server-side (`VIS-YYYYMMDD-XXXXX`) dan timestamp server `arrived_at`.
- **Active Visit Concurrency Guard**: Mencegah kunjungan aktif ganda pada pasien yang sama di dalam transaksi database dengan row locking dan opsi override ber-alasan.
- **Pembatalan Kunjungan Non-Destruktif**: Pembatalan kunjungan medis wajib menyertakan alasan dan dicatat pada log audit append-only.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk profil kesehatan dan registrasi kunjungan medis (`PatientHealthProfilePolicy`, `PatientAllergyPolicy`, `MedicalVisitPolicy`).
- **Tampilan UI Pelayanan Medis**: Profil Lengkap Pasien, Antrean Intake Kunjungan Medis, Form Registrasi Kedatangan, dan Detail Kunjungan Medis.
- **Feature Tests**: Pengujian Pest untuk `PatientHealthProfileTest` dan `MedicalVisitIntakeTest` (17 tests, 59 assertions passed).


### Added

- **Model Identitas ULID**: Implementasi tabel dan Model Eloquent `Person`, `User`, dan `Patient` dengan pemisahan entitas yang aman.
- **Rules Pasien**: Aturan kelayakan pasien untuk seluruh pengguna manusia dari Gate (termasuk admin) dan pengecualian untuk akun teknis/bot.
- **Sistem Role & Permission**: Skema `roles`, `permissions`, `model_has_roles`, `role_has_permissions` dengan Policy server-side (`UserPolicy`, `PersonPolicy`, `PatientPolicy`, `GateSyncPolicy`, `AuditLogPolicy`).
- **Keamanan Akses Medis**: Menerapkan aturan bahwa role Admin tidak otomatis memiliki akses rekam medis tanpa permission `view-patients`.
- **Fondasi Audit Append-Only**: Tabel `audit_logs` dan `AuditLogService` yang terdesinfeksi dari password/secret.
- **Gate SSO Contract & Fake Service**: Implementasi `GateClientContract`, `GateUserDTO`, dan `FakeGateClientService` dengan data sintetis.
- **Dry-Run Sync Engine**: Mesin simulasi dry-run `GateSyncDryRunService` non-mutating dengan 10 kategori klasifikasi.
- **UI Management Shell**: Halaman Direktori Person, Status Pasien, Akun Pengguna, Role & Permission, Gate Sync Preview, Konflik Identitas, dan Log Audit Viewer.
- **Feature Tests**: Pengujian Pest untuk `PersonUserPatientTest`, `PolicyAccessTest`, `DryRunSyncTest`, dan `AuditLogTest`.


### Added

- Inisialisasi repositori Git dan bootstrap fondasi Laravel 13, Livewire 4, Pest 4, Larastan, dan Vite.
- Implementasi sistem tema 3-mode (`light`, `dark`, `system`) dengan semantic design tokens (`--background`, `--surface`, `--primary`, dll.).
- Script anti-flicker tema yang dieksekusi sebelum *first paint*.
- Komponen switcher tema yang aksebel dan responsif dengan dukungan keyboard serta status persistence (`localStorage`).
- Layout App Shell modern dengan sidebar responsif, topbar, dan footer.
- Dashboard shell kosong dengan kartu statistik poskestren dan pengumuman SOP pelayanan santri sakit.
- Endpoint kesehatan `/health` yang mengembalikan status sistem JSON (DB, Cache, Storage).
- Pengujian otomatis Pest untuk Dashboard, HealthCheck, dan Theme Preference.
- Laporan preflight (`ENVIRONMENT-PREFLIGHT.md`), baseline graphify (`GRAPHIFY-BASELINE-REVIEW.md`), dan readiness review (`READINESS-REVIEW.md`).

### Fixed

- Membersihkan 6 file Markdown duplikat pada root directory yang sudah ada di folder `docs/`.

- Definisi awal domain pelayanan kesehatan santri berasrama.
- Rancangan MVP, arsitektur, keamanan, UI/UX, data, API, testing, delivery, ADR, dan Graphify.
- Dukungan tema light, dark, dan system pada requirement.
- Instruksi kerja AI melalui `AGENTS.md`.

### Changed

- Belum ada.

### Fixed

- Belum ada.

### Security

- Menetapkan requirement audit trail dan larangan hard delete catatan medis.

## [0.2.0] — 2026-08-05

### Added

- Model identitas `person -> user -> patient`.
- Kelayakan pasien untuk seluruh pengguna manusia dari Gate.
- Workflow sinkronisasi Gate yang idempotent dan dapat direkonsiliasi.
- Workflow konsultasi klinis jarak jauh ke Puskesmas/rumah sakit.
- Clinical consultation summary dan external clinical advice.
- Security governance untuk pertukaran data konsultasi.
- Panduan instalasi Graphify untuk macOS, Codex, Gemini, dan Antigravity.
- ADR pemisahan person/patient dan konsultasi klinis jarak jauh.

### Changed

- Scope aplikasi diperluas dari rekam medis santri menjadi rekam medis warga SABIRA dengan aturan operasional khusus santri berasrama.
- Admin dipisahkan sebagai permission; hanya akun administratif/teknis murni yang tidak menjadi pasien.
- Gate ditegaskan sebagai source of truth identitas dan tipe pengguna.
