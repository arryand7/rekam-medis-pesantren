---
id: DOC-PHASE-5A-ROUTE-AND-SCREEN-INVENTORY
title: "Phase 5A User-Facing Route & Screen Inventory"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-11
---

# Phase 5A User-Facing Route & Screen Inventory

Dokumen ini memetakan seluruh 117 rute aplikasi **SABIRA POSKESTREN Health** berdasarkan modul, peran pengguna, aksi primer/sekunder, alur kerja induk, serta status kesiapan UX.

---

## 1. Modul Autentikasi & Identitas (*Auth & Identity*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /login` | `GateOidcAuthController@login` | `web`, `guest` | `pages.auth.login` | Seluruh Pengguna | Masuk via Email/Username & Password | Masuk via SABIRA Gate SSO | Autentikasi Pengguna | ✅ Siap (Hybrid SSO & Password Toggle) |
| `POST /login` | `GateOidcAuthController@authenticate` | `web`, `guest` | N/A (Redirect) | Seluruh Pengguna | Validasi Kredensial & Regenerasi Sesi | Rate limiting 5x/menit | Autentikasi Pengguna | ✅ Siap (Audit log `local_login`) |
| `GET /auth/gate/callback` | `GateOidcAuthController@callback` | `web`, `guest` | N/A (Redirect) | Pengguna SSO | OIDC State/Nonce Verification & Login | Auto-redirect dashboard | Gate SSO Flow | ✅ Siap (CSRF/State Safe) |
| `GET /auth/gate/access-denied` | `GateOidcAuthController@accessDenied` | `web` | `pages.auth.access-denied` | Pengguna SSO | Informasi Entitlement Ditolak | Kembali ke Login | OIDC Governance | ✅ Siap |
| `POST /logout` | `GateOidcAuthController@logout` | `web`, `auth` | N/A (Redirect) | Pengguna Login | Akhiri Sesi Lokal & Hapus Cookie | Invalidate Sesi | Sesi Pengguna | ✅ Siap (CSRF Protected) |

---

## 2. Modul Dashboard Berbasis Peran (*Role-Aware Dashboards*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /` & `GET /dashboard` | `DashboardController@index` | `web`, `auth` | Multi-view dispatcher | Seluruh Staf | Router otomatis ke dashboard peran | Beralih menu sidebar | Entry Point Aplikasi | ✅ Siap (Role Dispatch) |
| `GET /dashboards/clinical` | `DashboardController@clinical` | `web`, `auth`, `view-clinical-dashboard` | `pages.dashboards.clinical` | Dokter & Perawat | Lihat Antrean Kunjungan Aktif | Buka Rekam Medis Pasien | Pelayanan Klinis | ✅ Siap (Quick Action Buttons) |
| `GET /dashboards/operational` | `DashboardController@operational` | `web`, `auth`, `view-operational-dashboard` | `pages.dashboards.operational` | Pembina Asrama | Lihat Pembatasan Aktivitas Santri | Konfirmasi Handoff Pulang | Pengawasan Asrama | ✅ Siap (Data Sanitized) |
| `GET /dashboards/management` | `DashboardController@management` | `web`, `auth`, `view-management-dashboard` | `pages.dashboards.management` | Mudir / Pimpinan | Pantau Agregat Kesehatan Ponpes | Filter rentang tanggal laporan | Tata Kelola Pesantren | ✅ Siap (Aggregate KPIs) |

---

## 3. Modul Pasien & Person (*Patient & Person Directory*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /people` | Closure / `pages.people.index` | `web`, `auth`, `view-people` | `pages.people.index` | Administrator / Petugas | Cari Data Person (Santri/Ustadz) | Filter Tipe Pengguna | Manajemen Direktori | ✅ Siap (Pagination & Search) |
| `GET /patients` | Closure / `pages.patients.index` | `web`, `auth`, `view-patients` | `pages.patients.index` | Petugas Medis | Cari Pasien & Rekam Medis | Registrasi Kunjungan Baru | Rekam Medis Santri | ✅ Siap (Filter & Quick Intake) |
| `GET /patients/{id}` | Closure / `pages.patients.show` | `web`, `auth`, `view-patients` | `pages.patients.show` | Petugas Medis | Tinjau Profil Kesehatan & Alergi | Lihat Riwayat Kunjungan | Rekam Medis Pasien | ✅ Siap (Patient Context Header) |

---

## 4. Modul Kunjungan & Pelayanan Klinis (*Visit & Clinical Workspace*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /visits` | Closure / `pages.visits.index` | `web`, `auth`, `view-medical-visits` | `pages.visits.index` | Tim Medis | Tinjau Antrean Pasien Aktif | Filter Status Kunjungan | Alur Rawat Jalan | ✅ Siap (Active Visit Table) |
| `GET /visits/create` | Closure / `pages.visits.create` | `web`, `auth`, `create-medical-visits` | `pages.visits.create` | Petugas Intake | Daftarkan Kunjungan Medis Baru | Pilih Pasien dari Autocomplete | Intake Kunjungan | ✅ Siap (Validation Feedback) |
| `POST /visits` | Closure / `MedicalVisitService@registerVisit` | `web`, `auth`, `create-medical-visits` | N/A (Redirect) | Petugas Intake | Simpan Kunjungan & Buka Workspace | Cetak Antrean | Intake Kunjungan | ✅ Siap (Active Visit Guard) |
| `GET /visits/{id}` | Closure / `pages.visits.show` | `web`, `auth`, `view-medical-visits` | `pages.visits.show` | Dokter / Perawat | Buka Workspace Kunjungan Lengkap | Navigasi Tab Tahapan Klinis | Workspace Kunjungan | ✅ Siap (Unified Stage Tabs) |
| `POST /visits/{id}/vital-signs` | Closure / `VitalSignService@record` | `web`, `auth`, `record-vital-signs` | N/A (Redirect) | Perawat / Bidan | Simpan Pencatatan Tanda Vital | Tinjau Riwayat Vital Sign | Pengkajian Awal | ✅ Siap (Unit Metrics Clearly Shown) |
| `GET /visits/{id}/assessment` | Closure / `pages.visits.assessment` | `web`, `auth`, `create-clinical-assessments` | `pages.visits.assessment` | Tenaga Medis | Input SOAP & Diagnosis Kerja | Simpan Draf Pengkajian | Pengkajian Klinis | ✅ Siap (Structured SOAP Form) |
| `POST /visits/{id}/assessment` | Closure / `ClinicalAssessmentService@record` | `web`, `auth`, `create-clinical-assessments` | N/A (Redirect) | Tenaga Medis | Finalisasi Pengkajian Klinis | Tambah Addendum | Pengkajian Klinis | ✅ Siap (Confirmation Modal) |
| `POST /visits/{id}/actions` | Closure / `ClinicalAssessmentService@recordAction` | `web`, `auth`, `record-initial-actions` | N/A (Redirect) | Perawat | Catat Tindakan Medis Awal | Perbarui Catatan Tindakan | Intervensi Medis | ✅ Siap |
| `POST /visits/{id}/cancel` | Closure / `MedicalVisitService@cancelVisit` | `web`, `auth`, `cancel-medical-visits` | N/A (Redirect) | Petugas Medis | Batalkan Kunjungan Tidak Sah | Masukkan Alasan Pembatalan | Koreksi Administratif | ✅ Siap (Destructive Guard) |

---

## 5. Modul Observasi Rawat Inap Sementara (*POSKESTREN Observation*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /observations` | Closure / `pages.observations.index` | `web`, `auth`, `view-observations` | `pages.observations.index` | Petugas Jaga | Pantau Santri di Ruang Observasi | Filter Bed / Status Aktif | Observasi Ruang Rawat | ✅ Siap (Bed Indicator) |
| `GET /observations/{id}` | Closure / `pages.observations.show` | `web`, `auth`, `view-observations` | `pages.observations.show` | Dokter & Perawat | Catat Monitoring Berkala | Lakukan Timbang Terima (Handover) | Observasi Pasien | ✅ Siap (Timeline View) |
| `POST /observations/{id}/monitoring` | Closure / `ObservationService@recordMonitoring` | `web`, `auth`, `record-observation-monitoring` | N/A (Redirect) | Perawat Jaga | Catat Tanda Vital & Kondisi Terkini | Tinjau Tren Perkembangan | Monitoring Berkala | ✅ Siap |
| `POST /observations/{id}/handover` | Closure / `ObservationService@recordHandover` | `web`, `auth`, `record-observation-handover` | N/A (Redirect) | Petugas Pergantian Shift | Serah Terima Shift Jaga | Catat Rekomendasi Lanjutan | Timbang Terima | ✅ Siap |
| `POST /observations/{id}/complete` | Closure / `ObservationService@completeObservation` | `web`, `auth`, `finalize-observation` | N/A (Redirect) | Dokter Penanggung Jawab | Selesaikan Masa Observasi | Tentukan Disposisi Akhir | Disposisi Observasi | ✅ Siap |

---

## 6. Modul Farmasi & Obat (*Pharmacy & Medication*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /pharmacy/inventory` | Closure / `pages.pharmacy.inventory` | `web`, `auth`, `view-pharmacy-inventory` | `pages.pharmacy.inventory` | Petugas Farmasi | Monitor Stok Obat & Batch Kadaluarsa | Filter Batch Karantina | Pengelolaan Stok | ✅ Siap (Low Stock Warning) |
| `GET /pharmacy/medicines` | Closure / `pages.pharmacy.medicines` | `web`, `auth`, `manage-medicines` | `pages.pharmacy.medicines` | Petugas Farmasi | Master Katalog Obat | Tambah Obat Baru | Katalog Farmasi | ✅ Siap |
| `GET /pharmacy/receipt/create` | Closure / `pages.pharmacy.receipt` | `web`, `auth`, `receive-pharmacy-stock` | `pages.pharmacy.receipt` | Petugas Farmasi | Input Penerimaan Obat Baru | Masukkan Nomor Batch & Exp Date | Penerimaan Stok | ✅ Siap |
| `GET /pharmacy/adjustments/create` | Closure / `pages.pharmacy.adjustments` | `web`, `auth`, `adjust-pharmacy-stock` | `pages.pharmacy.adjustments` | Apoteker / Petugas | Penyesuaian Stok Fisik (Opname) | Berikan Alasan Penyesuaian | Audit Stok Fisik | ✅ Siap (Append-Only Ledger) |
| `GET /visits/{id}/medications` | Closure / `pages.visits.medications` | `web`, `auth`, `prescribe-medications` | `pages.visits.medications` | Dokter & Farmasi | Resepkan Obat Kunjungan | Dispensing & Penyerahan Obat | Pelayanan Obat | ✅ Siap (Allergy Guard) |
| `POST /medication-orders/{id}/administer` | Closure / `MedicationService@administer` | `web`, `auth`, `administer-medications` | N/A (Redirect) | Perawat / Farmasi | Catat Pemberian Obat & Potong Stok | Verifikasi Batch & Dosis | Administrasi Obat | ✅ Siap (Atomic Stock Issue) |

---

## 7. Modul Konsultasi & Rujukan (*Consultation & Referral*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /consultations` | Closure / `pages.consultations.index` | `web`, `auth`, `view-consultations` | `pages.consultations.index` | Dokter Jaga | Kelola Konsultasi Medis Eksternal | Buat Pertanyaan Baru ke Faskes | Tele-Konsultasi | ✅ Siap |
| `POST /consultations/{id}/transmit` | Closure / `ClinicalConsultationService@transmit` | `web`, `auth`, `transmit-consultations` | N/A (Redirect) | Dokter Jaga | Kirim Ringkasan Klinis Terenkripsi | Catat Balasan Advice Medis | Konsultasi Klinis | ✅ Siap |
| `GET /referrals` | `ReferralController@index` | `web`, `auth`, `view-referrals` | `pages.referrals.index` | Tim Medis & Logistik | Monitor Status Rujukan Aktif | Tinjau Faskes Rujukan Tujuan | Rujukan Eksternal | ✅ Siap (Status Badge) |
| `GET /referrals/{id}` | `ReferralController@show` | `web`, `auth`, `view-referrals` | `pages.referrals.show` | Dokter / Pendamping | Perbarui Tahapan Rujukan | Unduh Surat Rujukan Resmi | Pelaksanaan Rujukan | ✅ Siap (Timeline & Document) |
| `POST /referrals/{id}/depart` | `ReferralDepartureController@store` | `web`, `auth`, `manage-referrals` | N/A (Redirect) | Sopir Ambulans / Pendamping | Catat Waktu & Armada Keberangkatan | Cek Kondisi Pasien Saat Berangkat | Logistik Rujukan | ✅ Siap |
| `POST /referrals/{id}/handover` | `ReferralHandoverController@store` | `web`, `auth`, `manage-referrals` | N/A (Redirect) | Pendamping Medis | Catat Serah Terima di Faskes Tujuan | Masukkan Nama Nakes Penerima | Serah Terima Rujukan | ✅ Siap |
| `POST /referrals/{id}/return` | `ReferralReturnController@store` | `web`, `auth`, `manage-referrals` | N/A (Redirect) | Pendamping Medis | Catat Kepulangan Pasien ke Ponpes | Input Resume Medis Rumah Sakit | Kepulangan Rujukan | ✅ Siap |
| `POST /referral-returns/{id}/review` | `ReferralReturnReviewController@store` | `web`, `auth`, `review-referral-returns` | N/A (Redirect) | Dokter POSKESTREN | Tinjau Rekomendasi RS & Buat Rencana Lanjutan | Tentukan Istirahat Santri | Review Kepulangan | ✅ Siap |

---

## 8. Modul Kepulangan, Pembatasan Aktivitas & Laporan (*Discharge & Reporting*)

| Rute & Metode | Controller / Action | Middleware / Otorisasi | View / Komponen | Pengguna Utama | Aksi Primer | Aksi Sekunder | Alur Kerja Induk | Kesiapan UX |
|---|---|---|---|---|---|---|---|---|
| `GET /discharges` | `VisitDischargeController@index` | `web`, `auth`, `view-discharges` | `pages.discharges.index` | Dokter & Pembina | Pantau Kepulangan Kunjungan | Cetak Surat Keterangan Istirahat | Kepulangan Medis | ✅ Siap |
| `GET /discharges/{id}` | `VisitDischargeController@show` | `web`, `auth`, `view-discharges` | `pages.discharges.show` | Dokter & Pembina | Tinjau Resume Pulang & Pembatasan | Kirim Handoff ke Asrama | Kepulangan Pasien | ✅ Siap |
| `GET /follow-up-plans` | `VisitFollowUpPlanController@index` | `web`, `auth`, `view-follow-up-plans` | `pages.discharges.follow-up-plans.index` | Petugas Medis | Pantau Jadwal Kontrol Pasien | Tandai Kontrol Selesai | Tindak Lanjut Pasien | ✅ Siap |
| `GET /operational-handoffs` | `ClinicalOperationalHandoffController@index` | `web`, `auth`, `view-operational-handoffs` | `pages.discharges.operational-handoffs.index` | Pengurus Asrama | Konfirmasi Penerimaan Santri Pulang | Baca Catatan Pembatasan Tugas | Handoff Asrama | ✅ Siap (Sanitized View) |
| `GET /reports` | `HealthReportController@index` | `web`, `auth`, `view-reports` | `pages.reports.index` | Pimpinan Pesantren | Unduh Laporan Morbiditas & Kunjungan | Filter Kategori & Periode Waktu | Pelaporan Kesehatan | ✅ Siap (Export Ready) |
