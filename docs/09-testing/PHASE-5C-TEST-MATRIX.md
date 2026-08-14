---
id: DOC-PHASE-5C-TEST-MATRIX
title: "Phase 5C Test Matrix — Dashboard, Reporting & Intelligence"
status: active
owner: "Antigravity AI / Ryand Arifriantoni"
last_updated: 2026-08-14
---

# Phase 5C Test Matrix — Dashboard, Reporting & Intelligence

Matriks pengujian otomatis dan verifikasi privasi untuk kapabilitas Phase 5C.

---

## 1. Feature Test Coverage Matrix

| Test Suite / Area | Skenario Uji | Target Verifikasi | Ekspektasi |
|---|---|---|---|
| **Clinical Dashboard** | Akses Tenaga Medis | `GET /dashboards/clinical` | Status 200, metrik akurat, antrean render deep links |
| **Clinical Dashboard** | Antrean Pengkajian | Pasien registered/waiting | Muncul di `waitingAssessmentQueue` |
| **Clinical Dashboard** | Antrean Observasi | Episode active | Muncul di `activeObservationQueue` |
| **Clinical Dashboard** | Antrean Konsultasi | Konsultasi advice received | Muncul di `pendingConsultationDecisionQueue` |
| **Clinical Dashboard** | Antrean Rujukan | Referral in progress & return | Muncul di `referralFollowUpQueue` |
| **Clinical Dashboard** | Antrean Kontrol | Follow-up due today | Muncul di `dueFollowUpQueue` |
| **Operational Dashboard** | Akses Pengasuh Asrama | `GET /dashboards/operational` | Status 200, tabel pembatasan tampil |
| **Operational Privacy** | Pengasuh Asrama Privacy Guard | Respons HTML & JSON | **Tidak ada diagnosis, kode ICD, SOAP, obat, vital signs** |
| **Operational Isolation**| Akses Rute Klinis oleh Pengasuh | `GET /dashboards/clinical`, `/visits` | Status **403 Forbidden** |
| **Pharmacy Dashboard** | Akses Tim Farmasi/Medis | `GET /dashboards/pharmacy` | Status 200, metrik batch kedaluwarsa & habis akurat |
| **Pharmacy Dashboard** | Deteksi Kedaluwarsa | Batch expired & near-expiry | Masuk kategori peringatan sesuai hari |
| **Management Dashboard** | Akses Pimpinan/Manajemen | `GET /dashboards/management` | Status 200, metrik agregat murni tampil |
| **Management Privacy** | Zero PII / Identitas Pasien | Respons HTML Manajemen | **Tidak ada nama santri, MRN, NIS, rekam medis individual** |
| **Management Access** | Isolasi Admin Teknis Murni | `admin` tanpa permission manajemen | Status **403 Forbidden** |
| **Date Range Filter** | Filter Rentang Tanggal | `?from=2026-08-01&to=2026-08-14` | Data teragregasi sesuai interval yang dipilih |
| **Date Range Filter** | Penanganan Range Tidak Valid | `?from=2026-08-14&to=2026-08-01` | Fallback aman tanpa crash |
| **Reports Hub & View** | Akses Laporan Klinis & Sensus | `GET /reports`, `GET /reports/view` | Status 200, KPI strip & data tabel paginated |
| **Report CSV Export** | Ekspor Sensus Kunjungan | `GET /reports/export?report_type=visit_census` | Status 200, Content-Type `text/csv`, metadata header lengkap |
| **Report Export Privacy** | Ekspor Laporan Manajemen | `GET /reports/export?report_type=management_aggregate` | Tidak membocorkan identitas pasien di berkas CSV |
| **Query Performance** | Upper-Bound Query Count | Jumlah baris bertambah 10x | Query count tetap stabil (O(1) / non-linear N+1) |

---

## 2. Responsive & Theme Verification Matrix

| Layar / View | 375x812 (Mobile) | 768x1024 (Tablet) | 1024x768 (Desktop) | 1440x900 (Large) | Dark Theme |
|---|---|---|---|---|---|
| `/dashboards/clinical` | Pending | Pending | Pending | Pending | Pending |
| `/dashboards/operational` | Pending | Pending | Pending | Pending | Pending |
| `/dashboards/pharmacy` | Pending | Pending | Pending | Pending | Pending |
| `/dashboards/management` | Pending | Pending | Pending | Pending | Pending |
| `/reports` | Pending | Pending | Pending | Pending | Pending |
| `/reports/view` | Pending | Pending | Pending | Pending | Pending |
