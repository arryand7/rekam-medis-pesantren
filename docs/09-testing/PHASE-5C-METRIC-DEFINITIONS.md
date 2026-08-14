---
id: DOC-PHASE-5C-METRIC-DEFINITIONS
title: "Phase 5C & 5C1 Metric and KPI Definitions"
status: active
owner: "Antigravity AI / Ryand Arifriantoni"
last_updated: 2026-08-14
---

# Phase 5C & 5C1 Metric and KPI Definitions

Dokumen ini mendefinisikan seluruh Key Performance Indicators (KPI) dan metrik statistik yang digunakan di seluruh dashboard dan laporan POSKESTREN SABIRA.

---

## 1. Definisi Metrik Klinis (Clinical KPIs)

### A. Visits Today (Kunjungan Hari Ini)
- **Definisi**: Jumlah seluruh pendaftaran kunjungan medis pada hari berjalan (00:00:00 s/d 23:59:59 WIB).
- **Field Sumber**: `medical_visits.created_at` (atau `arrived_at`).
- **Status Termasuk**: Seluruh status kecuali `cancelled`.
- **Privacy Scope**: `CLINICAL_DETAIL`.

### B. Waiting Assessment (Menunggu Pengkajian)
- **Definisi**: Jumlah pasien yang telah terdaftar tetapi belum selesai menjalani pengkajian awal oleh dokter/perawat.
- **Field Sumber**: `medical_visits.status IN ('registered', 'waiting_assessment')`.
- **Privacy Scope**: `CLINICAL_DETAIL`.

### C. Active Observations (Observasi Aktif)
- **Definisi**: Jumlah episode rawat istirahat yang saat ini sedang berlangsung di ruang observasi Poskestren.
- **Field Sumber**: `observation_episodes.status = 'active'`.
- **Privacy Scope**: `CLINICAL_DETAIL`.

### D. Pending External Advice Decision (Konsultasi Menunggu Keputusan)
- **Definisi**: Konsultasi eksternal yang telah memperoleh respon/anjuran dokter luar (`status = 'responded'`), namun belum ditindaklanjuti dengan penetapan keputusan klinis lokal Poskestren.
- **Field Sumber**: `clinical_consultations.status = 'responded' AND clinical_consultations.completed_at IS NULL`.
- **Privacy Scope**: `CLINICAL_DETAIL`.

### E. Follow-Up Due Today / Overdue (Kontrol Jatuh Tempo)
- **Definisi**: Rencana kontrol pasca rawat yang jatuh tempo pada hari ini atau telah melewati batas waktu dan belum diselesaikan.
- **Field Sumber**: `visit_follow_up_plans.status = 'planned' AND visit_follow_up_plans.due_at <= NOW()`.
- **Privacy Scope**: `CLINICAL_DETAIL`.

---

## 2. Definisi Metrik Farmasi (Pharmacy KPIs)

### A. Expired Batches (Batch Kedaluwarsa)
- **Definisi**: Jumlah batch obat dengan tanggal kedaluwarsa lebih kecil dari tanggal hari ini dan masih memiliki sisa stok (`current_quantity > 0`).
- **Field Sumber**: `medicine_batches.expiry_date < CURRENT_DATE AND medicine_batches.current_quantity > 0`.

### B. Near-Expiry Batches (Batch Hampir Kedaluwarsa)
- **Definisi**: Jumlah batch obat dengan tanggal kedaluwarsa dalam rentang jendela peringatan (`config('pharmacy.expiry_warning_days')`, default 30 hari ke depan) dan masih memiliki sisa stok.
- **Formula**: `medicine_batches.expiry_date BETWEEN CURRENT_DATE AND (CURRENT_DATE + interval config('pharmacy.expiry_warning_days')) AND current_quantity > 0`.
- **Status Kebijakan**: Default teknis 30 hari (`[PERLU DIKONFIRMASI DENGAN SOP FARMASI RESMI]`).

### C. Depleted Batches / Out of Stock (Batch Habis)
- **Definisi**: Jumlah batch obat aktif yang memiliki sisa stok sama dengan 0.
- **Field Sumber**: `medicine_batches.current_quantity <= 0`.

### D. Low Stock Medicines (Stok Obat Menipis)
- **Definisi**: Jumlah item master obat aktif yang total akumulasi kuantitas seluruh batchnya kurang dari atau sama dengan ambang batas `config('pharmacy.low_stock_threshold')`.
- **Ambang Batas**: Jika `config('pharmacy.low_stock_threshold') === null`, sistem menandai indikator sebagai `Belum Dikonfigurasi` (`[PERLU DIKONFIRMASI]`).

---

## 3. Definisi Metrik Manajemen & Eksekutif (Management KPIs)

### A. Total Visits Served (Total Kunjungan)
- **Definisi**: Agregat jumlah kunjungan medis dalam rentang tanggal yang dipilih.
- **Formula**: `COUNT(id) FROM medical_visits WHERE created_at BETWEEN from AND to AND status != 'cancelled'`.
- **Privacy Scope**: `MANAGEMENT_AGGREGATE` (tidak memuat identitas pasien).

### B. Unique Patients Served (Pasien Unik Dilayani)
- **Definisi**: Jumlah individu berbeda yang memanfaatkan layanan Poskestren dalam periode tersebut.
- **Formula**: `COUNT(DISTINCT patient_id) FROM medical_visits WHERE created_at BETWEEN from AND to AND status != 'cancelled'`.
- **Privacy Scope**: `MANAGEMENT_AGGREGATE`.

### C. Follow-Up Completion Rate (Tingkat Kepatuhan Kontrol)
- **Definisi**: Persentase rencana kontrol pasca kepulangan yang berhasil dilaksanakan.
- **Numerator**: `COUNT(id) FROM visit_follow_up_plans WHERE status = 'completed' AND created_at BETWEEN from AND to`.
- **Denominator**: `COUNT(id) FROM visit_follow_up_plans WHERE created_at BETWEEN from AND to`.
- **Zero Denominator Handling**: Jika `Denominator = 0`, sistem mengembalikan `null` dan UI merender `Belum ada data` (bukan 100%).

### D. Comparison Calculation (Perhitungan Komparasi Periode)
- **Formula**: `((Nilai_Sekarang - Nilai_Lalu) / Nilai_Lalu) * 100`.
- **Zero Denominator Handling**: Jika `Nilai_Lalu = 0`, sistem **DILARANG** menampilkan lonjakan persentase buatan. Sistem menampilkan string: `Tidak tersedia pembanding`.

---

## 4. Keselarasan Filter Laporan (Report Summary & Table Filter Alignment)

Semua KPI ringkasan pada pusat laporan (`HealthReportService::getReportSummary()`) menerapkan filter yang **identik** dengan query tabel laporan:
1. `start_date` dan `end_date`: Membatasi rentang pencatatan secara inklusif.
2. `status`: Menyaring status spesifik sesuai tipe laporan.
3. `search`: Menyaring kata kunci batch atau nama obat pada laporan farmasi.
4. **Formula Injection Sanitization**: Seluruh sel teks CSV yang diawali karakter `=, +, -, @, \t, \r` diprefix dengan `'` untuk mencegah eksekusi formula pada spreadsheet.
