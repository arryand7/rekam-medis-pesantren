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

## [0.14.0] — 2026-08-09 (Phase 3C2 Operational Outbox, Role Dashboards & Health Reports Foundation)

### Added

- **Transactional Integration Outbox (`integration_outbox_events`, `integration_delivery_attempts`)**: Pola outbox asinkron dengan locking baris MariaDB (`lockForUpdate()`), idempotensi unik, dan penanganan retry/dead-letter.
- **SABIRA Absensi Integration Contract & DTO (`AttendanceIntegrationContract`, `AttendanceHealthDispositionDTO`)**: Kontrak antarmuka dan DTO immutable dengan validasi otomatis terhadap kunci klinis terlarang (*forbidden keys guard*), didukung adapter sandbox `FakeAttendanceIntegration`.
- **Privacy Payload Profiles (`AttendanceDispositionPayloadBuilder`)**: Penegakan standar *Minimum Necessary* (zero clinical diagnoses/medications/allergies/vital signs) untuk Asrama, Guru, dan Absensi.
- **Operational Notifications & In-App User Inbox (`operational_notifications`, `user_notifications`)**: Alur notifikasi operasional terarah ke pembina asrama/guru dengan pelacakan konfirmasi, serta kotak masuk in-app untuk staf poskestren.
- **Role-Aware Dashboards (`dashboards.clinical`, `dashboards.management`, `dashboards.operational`)**: Dashboard klinis, dashboard manajemen statistik agregat (tanpa data individu), dan dashboard operasional santri berstatus restriksi.
- **Health Reports Foundation (`HealthReportService`)**: Sensus kunjungan, sensus observasi, sensus rujukan, laporan kepulangan & kontrol, laporan inventaris farmasi, dan audit pengiriman outbox.
- **Controllers, Policies, & Blade Views**: 6 controller dedicated di `App\Http\Controllers\*` (0 Route Closures), 13 permission baru, 5 policies, 4 form requests, dan 11 view responsif light & dark theme.
- **Test Suite Expansion**: 23 feature test baru (total 134 tests, 526 assertions, 100% passed di MariaDB).

## [0.13.0] — 2026-08-09 (Phase 3C1 Visit Discharge, Follow-up & Operational Handoff)


### Added

- **Visit Discharge Domain Aggregate (`visit_discharges`)**: Model kepulangan klinis ULID lengkap dengan penutupan kunjungan medis atomik, status lifecycle (`draft`, `finalized`, `amended`, `entered_in_error`), rekomendasi aktivitas, anjuran istirahat, dan catatan batasan.
- **Discharge Readiness Engine (`EvaluateVisitDischargeReadinessAction`)**: Validasi prasyarat teknis/administrasi penutupan kunjungan (pengkajian final, observasi/rujukan tuntas, peringatan obat aktif tanpa auto-discontinue).
- **Follow-Up Planning (`visit_follow_up_plans`)**: Perencanaan jadwal kontrol ulang berstruktur, penanggung jawab, dan penyelesaian/pembatalan manual berizin dan diaudit.
- **Activity Restrictions (`activity_restrictions`)**: Penerbitan surat/rekomendasi istirahat, bed rest, dan pembatasan aktivitas berjangka waktu.
- **Internal Operational Handoff (`clinical_operational_handoffs`)**: Serah terima instruksi perawatan internal ke pengasuh asrama/guru berprinsip *minimum-necessary privacy* dengan pelacakan konfirmasi penerimaan (*acknowledgement*).
- **Private Discharge Documents (`discharge_documents`)**: Disk penyimpanan privat `storage/app/private/discharges` dengan nama berkas ULID opaque, SHA-256 hash checksum, rate limiting (`throttle:30,1`), dan audit unduhan.
- **Controller & Policy Suite**: 5 controller dedicated di `App\Http\Controllers\Discharge\*` (0 Route Closures), 9 permission baru, Form Requests, dan Policies.
- **Test Suite**: 26 feature tests baru (total 111 tests, 408 assertions, 100% passed on MariaDB).

## [0.12.0] — 2026-08-09 (Phase 3B Hardening & Final Validation)


### Added

- **Controller & Policy Refactoring**: 9 controller dedicated di `App\Http\Controllers\Referral` menggantikan seluruh 11 route closure rujukan dengan otorisasi Policy server-side (`$this->authorize()`).
- **Form Request Validations**: 6 Form Requests terstruktur (`StoreReferralRequest`, `StoreReferralTransportRequest`, `StoreReferralCompanionRequest`, `StoreReferralStatusEventRequest`, `StoreReferralReturnRequest`, `StoreReferralReturnReviewRequest`) dengan isolasi mutasi data server-authoritative.
- **Private Referral Documents (`referral_documents`)**: Disk storage privat `storage/app/private/referrals`, filename opaque berbasis ULID, integritas hash SHA-256, proteksi traversal path, dan audit unduhan berkas.
- **MariaDB Concurrency Validation**: Bukti empiris 4 invariant concurrency pada MariaDB 10.4.28 (`poskestren_health_test`, InnoDB, REPEATABLE-READ).
- **Test Suite Expansion**: Penambahan suite tes otorisasi, dokumen private, dan concurrency (total 85 tests, 258 assertions, 100% passed on MariaDB).

### Security

- Seluruh mutasi data rujukan diverifikasi melalui Policy server-side.
- Endpoint dokumen rujukan dilindungi otorisasi, rate limiting (`throttle:30,1`), dan audit log per unduhan.
- Endpoint login stub tidak mengautentikasi user dan tidak menerima eskalasi role.
- Tidak ada URL publik untuk dokumen rujukan santri.

## [0.11.0] — 2026-08-05 (Phase 3B)


### Added

- **Actual Referral Workflow**: Tabel `referrals` dengan state machine lengkap, one-active-referral guard (pessimistic lock), dan referral number concurrency-safe (ULID suffix).
- **Referral Versions (Immutable Snapshot)**: Tabel `referral_versions` dengan SHA256 checksum dan payload minimum-necessary untuk dokumen rujukan.
- **Referral Transport**: Tabel `referral_transports` untuk pengaturan kendaraan/pengemudi dengan status tracking.
- **Referral Companions**: Tabel `referral_companions` dengan primary companion uniqueness enforcement.
- **Clinical Handover**: Tabel `referral_handovers` dengan idempotency key — handiff ≠ acceptance.
- **Destination Status Events**: Tabel `referral_status_events` untuk pelacakan status destinasi (arrived/accepted/declined) secara terpisah dari handover.
- **Return from Referral**: Tabel `referral_returns` — satu kepulangan per rujukan, timestamps server-authoritative, hasil eksternal tidak otomatis mutasi diagnosis/obat lokal.
- **Local Return Review**: Tabel `referral_return_reviews` — tinjauan klinis lokal terpisah, tidak membuat discharge otomatis.
- **ReferralService**: Layanan bisnis rujukan dengan seluruh state transitions, audit trail, dan invariant keamanan.
- **ReferralPolicy**: Otorisasi granular 12 operasi rujukan.
- **Routes & Views**: CRUD referral, transport, companion, handover, status event, return, dan return review.
- **Phase 3B Tests**: 15 feature tests baru (58 total, 201 assertions) — semua lulus ✅.

### Security

- Emergency referral tidak tertahan oleh konsultasi atau persetujuan administratif.
- External diagnosis/obat dari kepulangan tidak otomatis memodifikasi rekam medis lokal.
- Tidak ada discharge otomatis dari tinjauan kepulangan.

### Added

- **Master Mitra Layanan Kesehatan (Healthcare Partners & Contacts)**: Tabel `healthcare_partners` dan `healthcare_partner_contacts` untuk mengelola direktori Faskes (Puskesmas/RS) dan dokter/kontak medis mitra rujukan.
- **Agregat Konsultasi Klinis Eksternal (Clinical Consultations)**: Tabel `clinical_consultations` untuk mengkomunikasikan pertanyaan klinis profesional antar-tenaga kesehatan.
- **Versioned Summary Snapshot Payload**: Tabel `clinical_consultation_versions` yang menyimpan snapshot ringkasan rekam medis terstruktur versi 1 dengan verifikasi integritas hash sha256 `checksum`.
- **Abstraksi Transmisi Pengiriman (Transport Abstraction)**: Interface `ClinicalConsultationTransportContract` & `FakeClinicalConsultationTransport` dengan log transmisi `clinical_consultation_transmissions` dan idempotency.
- **Pencatatan Jawaban / Advice Klinis Eksternal**: Tabel `external_clinical_advices` untuk mencatat respons nasehat klinis dari dokter faskes mitra dengan atribusi terstruktur.
- **Penetapan Keputusan Klinis Lokal**: Tabel `consultation_local_decisions` untuk memformulasikan keputusan perawatan lokal Poskestren secara berwenang.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk mitra faskes dan konsultasi eksternal (`HealthcarePartnerPolicy`, `ClinicalConsultationPolicy`).
- **Consultation UI Shell**: Halaman direktori faskes mitra (`/healthcare-partners`), antrean konsultasi (`/consultations`), form composer (`/visits/{id}/consultations/create`), dan detail konsultasi (`/consultations/{id}`) dengan transmisi log dan form nasehat & keputusan lokal.
- **Feature Tests**: Pengujian Pest untuk `HealthcarePartnerTest`, `ClinicalConsultationTest`, `ConsultationTransmissionTest`, dan `ExternalAdviceAndLocalDecisionTest` (43 tests, 141 assertions passed).


### Added

- **Instruksi Obat Terstruktur (Medication Orders)**: Tabel `medication_orders` dan `MedicationService::createOrder` untuk mencatat instruksi obat internal Poskestren.
- **Penelusuran Alergi Pasien (Safety Acknowledgement)**: Tabel `medication_safety_acknowledgements` untuk mencatat konfirmasi alasan klinis penelusuran alergi aktif pasien.
- **Pencatatan Pemberian Obat (Medication Administration)**: Tabel `medication_administrations` untuk mengelola status pemberian obat (`scheduled`, `administered`, `held`, `refused`, `missed`, `cancelled`, `entered_in_error`).
- **Pengeluaran Stok Obat Atomik (Atomic Stock Issue)**: Pengurangan stok obat secara atomik pada tingkat transaksi database hanya saat status bertransisi menjadi `administered` (movement_type `medication_administration_issue`).
- **Koreksi Pemberian Obat & Reversal Stok**: Pembatalan catatan pemberian (`entered_in_error`) mengembalikan sisa stok batch secara atomik dan mencatat `medication_administration_reversal`.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk instruksi dan pemberian obat (`MedicationOrderPolicy`, `MedicationAdministrationPolicy`).
- **Medication Workspace UI Shell**: Workspace Pemberian Obat Santri (`/visits/{id}/medications`) dengan Form Order Obat, Warning Alergi Aktif, Jadwal Pemberian, Modal Konfirmasi Pilih Batch, dan Riwayat Pemberian.
- **Feature Tests**: Pengujian Pest untuk `MedicationOrderTest`, `MedicationAdministrationTest`, `MedicationStatusTest`, dan `MedicationReversalTest` (39 tests, 125 assertions passed).


### Added

- **Master Data Obat (Medicine Master)**: Tabel `medicines` dan `PharmacyService::createMedicine` untuk mengelola katalog obat Poskestren.
- **Master Lokasi Penyimpanan Stok**: Tabel `stock_locations` dengan lokasi default `Ruang Apotek Utama Poskestren`.
- **Tracking Batch & Kedaluwarsa**: Tabel `medicine_batches` untuk pemantauan masa aktif, kedaluwarsa, dan status quarantine/depleted batch obat.
- **Append-Only Stock Ledger (`stock_movements`)**: Tabel mutasi stok append-only untuk pencatatan `receipt`, `adjustment_in`, `adjustment_out`, dan `reversal`.
- **Pencegahan Stok Negatif (No Negative Stock Guard)**: Validasi tingkat database transaction yang menolak mutasi pengeluaran melebihi sisa stok batch.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk master obat, batch, lokasi, dan mutasi stok (`MedicinePolicy`, `MedicineBatchPolicy`, `StockMovementPolicy`, `StockLocationPolicy`).
- **Pharmacy UI Shell**: Antarmuka master obat (`/pharmacy/medicines`), dashboard stok & batch (`/pharmacy/inventory`), form penerimaan stok (`/pharmacy/receipt/create`), dan form penyesuaian stok (`/pharmacy/adjustments/create`).
- **Feature Tests**: Pengujian Pest untuk `MedicineMasterTest`, `StockReceiptTest`, `StockAdjustmentTest`, dan `StockReversalTest` (33 tests, 107 assertions passed).


### Added

- **Episode Observasi Poskestren (Observation Episodes)**: Tabel `observation_episodes` dan `ObservationService` untuk mengelola masa pemantauan tirah baring santri di Poskestren.
- **Active Observation Guard**: Penguncian transaksi database untuk memastikan 1 episode observasi aktif per kunjungan medis.
- **Pemantauan Berkala (Periodic Monitoring)**: Tabel `observation_records` untuk mencatat ringkasan kondisi santri dan evaluasi berkala.
- **Shift Handover & Transfer Tanggung Jawab Atomik**: Tabel `observation_handovers` untuk serah terima tugas jaga antarpetugas, yang secara atomik mengubah `responsible_officer_id` saat disetujui (*acknowledged*).
- **Hasil Observasi (Outcome) & State Machine**: Penyelasaian episode observasi dengan rekomendasi outcome dan transisi status kunjungan ke `under_observation` lalu `observation_completed`.
- **Hak Akses & Policy Baru**: Otorisasi server-side untuk episode observasi, pemantauan berkala, dan handover shift (`ObservationEpisodePolicy`, `ObservationRecordPolicy`, `ObservationHandoverPolicy`).
- **Observation UI Shell**: Antarmuka antrean observasi (`/observations`) dan workspace observasi santri (`/observations/{id}`).
- **Feature Tests**: Pengujian Pest untuk `ObservationEpisodeTest`, `ObservationMonitoringTest`, `ObservationHandoverTest`, dan `ObservationOutcomeTest` (27 tests, 89 assertions passed).


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
