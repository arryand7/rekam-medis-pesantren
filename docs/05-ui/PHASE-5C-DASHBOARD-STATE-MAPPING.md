---
id: DOC-PHASE-5C-DASHBOARD-STATE-MAPPING
title: "Phase 5C Dashboard State & Work Queue Mapping"
status: complete
owner: "Antigravity AI / Ryand Arifriantoni"
last_updated: 2026-08-14
---

# Phase 5C Dashboard State & Work Queue Mapping

Dokumen ini memetakan setiap status tampilan dan antrean kerja (*work queue*) ke field model domain dan invariant basis data yang sebenarnya.

---

## 1. Clinical Dashboard Work Queues

| Queue Label | Model Sumber | Kondisi Query / Status Domain | Urutan / Sort | Aksi Utama (Deep Link) |
|---|---|---|---|---|
| **Menunggu Pengkajian (Waiting Assessment)** | `MedicalVisit` | `status IN ('registered', 'waiting_assessment')` | `arrived_at ASC` atau `created_at ASC` | Buka Form Pengkajian (`/visits/{id}/assessment`) |
| **Episode Observasi Aktif (Active Observation)** | `ObservationEpisode` | `status = 'active'` | `next_monitoring_due_at ASC` atau `started_at ASC` | Buka Lembar Observasi (`/observations/{id}`) |
| **Konsultasi Menunggu Keputusan Lokal (Pending Local Decision)** | `ClinicalConsultation` | `status = 'responded'` AND `NOT EXISTS local decision` | `updated_at ASC` | Buka Telaah Konsultasi (`/consultations/{id}`) |
| **Rujukan Aktif & Telaah Kepulangan (Referral Follow-up)** | `Referral` | `status IN ('prepared', 'approved', 'ready_to_depart', 'departed', 'arrived', 'accepted', 'under_external_care', 'returned')` | `initiated_at DESC` | Buka Timeline Rujukan (`/referrals/{id}`) |
| **Jadwal Kontrol Jatuh Tempo (Follow-Up Due)** | `VisitFollowUpPlan` | `status = 'planned' AND due_at <= NOW()` | `due_at ASC` | Buka Follow-up Kunjungan (`/discharges/workspace?visit_id={id}`) |

---

## 2. Operational / Asrama Dashboard State Mapping

| Section / Card | Model Sumber | Kondisi Query / Filter | Privasi Scope | Field yang Ditampilkan |
|---|---|---|---|---|
| **Santri dalam Pembatasan Aktivitas** | `ActivityRestriction` (atau `VisitDischarge`) | `is_active = true` AND `effective_until >= NOW()` | `OPERATIONAL_MINIMUM_NECESSARY` | Nama Santri, Tipe Pembatasan, Masa Berlaku, Catatan Praktis Asrama, Aktivitas Diperbolehkan |
| **Serah Terima Menunggu Konfirmasi** | `OperationalNotification` | `status = 'prepared' AND recipient_type = 'dorm_supervisor'` | `OPERATIONAL_MINIMUM_NECESSARY` | Nama Santri, Tipe Notifikasi, Prioritas, Waktu Dibuat, Tombol Konfirmasi |

---

## 3. Pharmacy Dashboard State Mapping

| Section / Card | Model Sumber | Kondisi Query | Ambang Batas (Threshold) |
|---|---|---|---|
| **Batch Obat Kedaluwarsa** | `MedicineBatch` | `expiry_date < NOW()->toDateString() AND current_quantity > 0` | Otomatis sesuai tanggal kalender |
| **Batch Hampir Kedaluwarsa** | `MedicineBatch` | `expiry_date BETWEEN NOW() AND NOW()->addDays($warningDays)` | Konfigurasi `config('pharmacy.expiry_warning_days', 30)` |
| **Batch Stok Habis (Depleted)** | `MedicineBatch` | `current_quantity <= 0` | Jumlah = 0 |
| **Stok Menipis (Low Stock)** | `Medicine` | `SUM(batches.current_quantity) <= $threshold` | `config('pharmacy.low_stock_threshold')` (Status: Belum Dikonfigurasi bila null) |
| **Mutasi Buku Besar Hari Ini** | `StockMovement` | `created_at >= NOW()->startOfDay()` | Seluruh mutasi masuk, keluar, penyesuaian, dan reversal |

---

## 4. Management Dashboard Metrics & Trend Mapping

| Metrik KPI | Model Sumber | Perhitungan Numerator / Agregat | Penanganan Pembagi / Komparasi |
|---|---|---|---|
| **Total Kunjungan Medis** | `MedicalVisit` | `COUNT(id) WHERE created_at BETWEEN from AND to AND status != 'cancelled'` | Komparasi terhadap durasi hari periode sebelumnya |
| **Pasien Unik Dilayani** | `MedicalVisit` | `COUNT(DISTINCT patient_id) WHERE created_at BETWEEN from AND to AND status != 'cancelled'` | Bebas identitas individu |
| **Episode Observasi** | `ObservationEpisode` | `COUNT(id) WHERE created_at BETWEEN from AND to` | Agregat murni |
| **Rujukan Eksternal** | `Referral` | `COUNT(id) WHERE created_at BETWEEN from AND to` | Agregat murni |
| **Kepulangan Selesai** | `VisitDischarge` | `COUNT(id) WHERE status = 'finalized' AND created_at BETWEEN from AND to` | Agregat murni |
| **Tingkat Kepatuhan Kontrol** | `VisitFollowUpPlan` | `(COUNT(status='completed') / COUNT(*)) * 100` | `null` / `Belum ada data` jika denominator = 0 |
| **Volume Mutasi Farmasi** | `StockMovement` | `COUNT(id) WHERE created_at BETWEEN from AND to` | Agregat murni |
