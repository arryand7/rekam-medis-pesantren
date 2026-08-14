---
id: DOC-UI-PHASE5C-001
title: "Panduan UI & Alur Kerja Dashboard & Laporan Operasional (Phase 5C)"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-14
---

# Panduan UI & Alur Kerja Dashboard & Laporan Operasional (Phase 5C)

Dokumen ini mendokumentasikan implementasi antarmuka pengguna, *work queues*, dan modul pelaporan operasional terstruktur pada POSKESTREN Sabira.

## 1. Arsitektur Role-Based Dashboard

Aplikasi menyediakan 4 antarmuka dashboard terisolasi sesuai kewenangan pengguna:

```
                  /dashboards (Role Router)
                             |
         +-------------------+-------------------+-------------------+
         |                   |                   |                   |
         v                   v                   v                   v
  [Dashboard Klinis]   [Dashboard Farmasi]  [Dashboard Operasional] [Dashboard Eksekutif]
  - Petugas Medis      - Apoteker / Farmasi - Pengasuh Asrama       - Pimpinan / Admin
  - Dokter / Perawat   - Pengelola Obat     - Wali Kelas / Guru     - Pengurus Pesantren
```

---

## 2. Dashboard Klinis (`/dashboards/clinical`)

Dirancang sebagai kokpit kerja utama tenaga kesehatan di poskestren dengan agregasi metrik harian dan 5 antrean kerja (*actionable work queues*):

### Metrik KPI Harian
1. **Kunjungan Hari Ini**: Jumlah pasien masuk hari ini (kecuali yang dibatalkan).
2. **Menunggu Pengkajian**: Pasien dalam antrean awal pemeriksaan medis.
3. **Observasi Aktif**: Pasien yang sedang menjalani observasi rawat istirahat.
4. **Rujukan Berjalan**: Pasien yang sedang dalam proses penanganan di faskes rujukan.
5. **Advice Menunggu Aksi**: Kasus konsultasi jarak jauh dengan jawaban faskes mitra yang perlu telaah/keputusan klinis lokal.
6. **Kontrol / Follow-Up**: Pasien dengan jadwal kontrol klinis jatuh tempo hari ini atau terlewat.

### 5 Work Queues Interaktif
1. **Menunggu Pengkajian Awal**: Menampilkan nama santri, No. RM, keluhan utama, waktu tunggu, dan tombol aksi langsung `Mulai Periksa`.
2. **Episode Observasi Rawat Istirahat**: Menampilkan label bed/ruangan, waktu mulai, interval monitoring, waktu jatuh tempo pemeriksaan tanda vital berikutnya, dan tombol `Buka Lembar`.
3. **Saran Tele-Konsultasi Menunggu Aksi**: Menampilkan nama mitra faskes, tingkat urgensi (*routine/urgent/emergency*), waktu respons, dan tombol `Tinjau Advice`.
4. **Rujukan Eksternal & Telaah Kepulangan**: Menampilkan faskes rujukan, status rujukan, tingkat urgensi, dan tombol `Status`.
5. **Jadwal Kontrol & Follow-Up Jatuh Tempo**: Tabel interaktif jadwal kontrol dengan indikator visual keterlambatan (*overdue*) dan tautan ke lembar kepulangan.

---

## 3. Dashboard Farmasi & Inventaris Obat (`/dashboards/pharmacy`)

Dirancang untuk menjaga ketersediaan dan keamanan obat sesuai prinsip FEFO (*First Expired, First Out*):

### Indikator Farmasi
- **Kedaluwarsa**: Jumlah batch obat yang telah melewati tanggal kedaluwarsa dan wajib dikarantina.
- **Near-Expiry**: Jumlah batch obat dalam jendela peringatan (default &le; 30 hari).
- **Batch Habis (Depleted)**: Jumlah batch yang saldo fisiknya = 0.
- **Stok Obat Menipis**: Jumlah jenis obat yang total stoknya berada di bawah ambang batas minimum.
- **Dispensa & Mutasi Hari Ini**: Aktivitas pengeluaran dan mutasi stok harian.

### Komponen Tampilan
- **Tabel Batch Kedaluwarsa & Near-Expiry**: Menampilkan nama obat, nomor batch, lokasi simpan, sisa stok, tanggal kedaluwarsa, dan badge status visual.
- **Tabel Batch Habis**: Memantau batch yang habis untuk kebutuhan restock/pengadaan.
- **Buku Besar Mutasi (*Append-Only*)**: Cuplikan 15 mutasi terakhir dari tabel `stock_movements` (penerimaan, dispensa, adjustment, pemusnahan) lengkap dengan nama petugas pencatat.

---

## 4. Dashboard Operasional Asrama & Guru (`/dashboards/operational`)

Dirancang dengan prinsip **Minimum Necessary** dan perlindungan privasi medis santri:

### Prinsip Keamanan & Privasi
- **Nol Narasi Klinis**: Tidak menampilkan diagnosis medis, anamnesis SOAP, atau riwayat obat sensitif.
- **Fokus pada Petunjuk Praktis**: Hanya menampilkan anjuran istirahat (misal: *Bed Rest*, *Light Duty*), pembatasan aktivitas fisik/olahraga, masa berlaku, dan catatan teknis asrama.
- **Konfirmasi Serah Terima**: Memantau notifikasi dan serah terima instruksi perawatan baru yang belum dikonfirmasi oleh pembina asrama/guru.

---

## 5. Dashboard Manajemen Eksekutif (`/dashboards/management`)

Dirancang untuk pimpinan yayasan dan pengurus pesantren guna evaluasi manajerial berbasis angka statistik agregat:

### Fitur Utama
- **Date Range Preset & Custom Toolbar**: Pilihan filter cepat (*Hari Ini, 7 Hari, 30 Hari, Bulan Ini*) serta input kalender *From - To*.
- **Perbandingan Periode Sebelumnya**: Menghitung persentase perubahan volume kunjungan terhadap periode sebelumnya secara proporsional.
- **Visualisasi Tren Volume**: Grafik batang visual yang ringan dan aksesibel dengan ringkasan tooltip dan tabel rincian data (*Accessible Tabular Fallback*).
- **Rasio Observasi & Rujukan**: Evaluasi efektivitas penanganan lokal poskestren vs kebutuhan rujukan eksternal.
- **Tingkat Kepatuhan Kontrol**: Persentase kepatuhan santri menyelesaikan rencana tindak lanjut / kontrol pasca-kepulangan.
- **Jaminan Privasi Penuh**: Dashboard eksekutif tidak menampilkan nama pasien, No. RM, NIS, ataupun catatan SOAP individual.

---

## 6. Pusat Laporan & Ekspor CSV (`/reports`)

Modul pelaporan terstruktur dengan 6 tipe sensus:
1. `visit_census`: Sensus Kunjungan Medis.
2. `observation_census`: Sensus Observasi Poskestren.
3. `referral_census`: Sensus Rujukan Eksternal.
4. `discharge_followup`: Laporan Kepulangan & Rencana Kontrol.
5. `pharmacy_stock`: Laporan Inventaris Stok Obat & Kedaluwarsa.
6. `integration_delivery`: Laporan Delivery Outbox Integrasi.

### Spesifikasi Ekspor CSV
- **Streaming Response**: Pengambilan data secara bertahap (*chunked*) tanpa membebani memori server pada dataset besar.
- **Excel UTF-8 BOM (`\xEF\xBB\xBF`)**: Memastikan file CSV terbuka rapi di Microsoft Excel tanpa masalah karakter encoding.
- **Header Metadata Audit**: Blok komentar pembuka berisi judul laporan, tanggal/waktu ekspor, nama petugas pengekspor, dan parameter filter yang digunakan.
- **Audit Logging**: Setiap aksi ekspor otomatis tercatat dalam tabel `audit_logs` untuk akuntabilitas.
