---
id: DOC-PHASE-5C-BEFORE-IMPLEMENTATION-AUDIT
title: "Phase 5C Pre-Implementation Audit — Dashboard & Reporting"
status: complete
owner: "Antigravity AI / Ryand Arifriantoni"
last_updated: 2026-08-14
---

# Phase 5C Pre-Implementation Audit — Dashboard & Reporting

Audit dilaksanakan pada 2026-08-14 terhadap baseline `3153e6a47658b4f42d6d588befa45bbef822cd36` (v0.20.2).

---

## 1. Status Komponen Eksisting

| Komponen / Fitur | Status Awal | Klasifikasi | Analisis Kesenjangan (Gap Analysis) |
|---|---|---|---|
| **Role Dashboard Routing (`DashboardController@index`)** | Ada | `EXISTING-PARTIAL` | Mendukung redirect ke `clinical`, `operational`, `management`, tapi belum memiliki rute khusus `pharmacy`. |
| **Clinical Dashboard (`/dashboards/clinical`)** | Ada | `EXISTING-PARTIAL` | Hanya menampilkan 8 summary counter card statis; **belum memiliki actionable work queue list** (antrean pengkajian, observasi aktif, advice menunggu keputusan lokal, rujukan aktif & telaah kepulangan, jadwal kontrol due). |
| **Operational Dashboard (`/dashboards/operational`)** | Ada | `EXISTING-PARTIAL` | Menampilkan tabel pembatasan aktivitas, namun belum memiliki filter status/kategori, pagination, serta aksi cepat konfirmasi handoff. Privasi minimum-necessary sudah terbukti baik. |
| **Pharmacy Dashboard (`/dashboards/pharmacy`)** | Belum Ada | `NOT-IMPLEMENTED` | Belum ada controller method, query, ataupun view blade khusus dashboard farmasi. |
| **Management Dashboard (`/dashboards/management`)** | Ada | `EXISTING-PARTIAL` | Memiliki counter agregat dasar tapi date range hardcoded (30 hari terakhir) tanpa date range selector toolbar, tanpa visualisasi tren, dan tanpa penanganan rasio komparasi periode sebelumnya. |
| **Health Report Hub (`/reports`)** | Ada | `EXISTING-COMPLETE` | Memiliki daftar jenis laporan klinis (kunjungan, observasi, rujukan, kepulangan, stok farmasi, integrasi). |
| **Health Report Detail (`/reports/view`)** | Ada | `EXISTING-PARTIAL` | Menampilkan tabel paginasi per jenis laporan, namun belum memiliki header ringkasan metrik KPI dan belum mendukung ekspor CSV dengan metadata. |
| **Report Export Capability (`/reports/export`)** | Belum Ada | `NOT-IMPLEMENTED` | Belum ada rute ekspor streaming CSV berizin (`export-health-reports`) yang menjaga filter aktif dan privasi agregat. |

---

## 2. Audit Hak Akses & Gate Otorisasi

| Ability / Permission | Terdaftar di DB / Gate | Digunakan di Controller / Policy | Kebutuhan Penyesuaian |
|---|---|---|---|
| `view-clinical-dashboard` | Ya | `DashboardPolicy@viewClinical` | Dipertahankan |
| `view-operational-dashboard` | Ya | `DashboardPolicy@viewOperational` | Dipertahankan |
| `view-management-dashboard` | Ya | `DashboardPolicy@viewManagement` | Dipertahankan |
| `view-pharmacy-dashboard` | Belum | Belum ada | **Perlu ditambahkan** ke `AppServiceProvider` & `DashboardPolicy@viewPharmacy` |
| `view-health-reports` | Ya | `HealthReportController` | Dipertahankan |
| `export-health-reports` | Terdaftar di Gate | Belum ada Controller action | **Perlu dihubungkan** ke `HealthReportController@export` |

---

## 3. Rencana Refactoring Query & Arsitektur

Untuk menghindari kueri agregat berat yang berserakan di dalam Blade atau Controller, seluruh pengambilan data dan komputasi metrik dipisahkan ke dalam dedicated Query Classes:
1. `App\Queries\Dashboard\ClinicalDashboardQuery`
2. `App\Queries\Dashboard\OperationalDashboardQuery`
3. `App\Queries\Dashboard\PharmacyDashboardQuery`
4. `App\Queries\Dashboard\ManagementDashboardQuery`
