---
id: DOC-POSKESTREN-DAILY-OPERATIONS-SOP
title: "POSKESTREN Daily Operations Standard Operating Procedure (SOP)"
status: ACTIVE
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# POSKESTREN Daily Operations Standard Operating Procedure (SOP)

## 1. Awal Hari (*Start of Day*) — Pukul 07:00 WIB

1. **Pemeriksaan Health Probes**:
   - Buka endpoint `https://poskestren.sabira.id/health/ready` untuk memastikan database, cache, dan penyimpanan privat terhubung normal.
2. **Review Dashboard Klinis & Antrean**:
   - Petugas medis memeriksa antrean santri kontrol ulang (*follow-up due*) hari ini dan santri yang masih berada di ruang observasi poskestren.
3. **Pemeriksaan Stok Obat Minimum**:
   - Petugas farmasi memeriksa peringatan obat yang mendekati batas stok minimum (`minimum_stock`) dan tanggal kedaluwarsa batch.
4. **Verifikasi Antrean Outbox & Integrasi**:
   - Pastikan tidak ada event outbox tertahan di status `failed` atau `dead_letter`.

---

## 2. Selama Jam Operasional Layanan — Pukul 07:30 - 21:00 WIB

1. **Pendaftaran Kunjungan Santri (*Intake*)**:
   - Cari santri berdasarkan NIS atau nama lengkap.
   - Catat keluhan utama dan asal asrama/lokasi pelapor.
2. **Pemeriksaan Tanda Vital & Pengkajian Medis**:
   - Catat suhu tubuh, tensi, nadi, pernapasan, dan saturasi oksigen.
   - Dokter/perawat melakukan anamnesis, pemeriksaan fisik, dan mengisi draft pengkajian klinis.
3. **Disposisi & Penanganan**:
   - **Boleh Kembali ke Asrama**: Diberikan obat bila perlu, buat ringkasan pulang (*discharge*).
   - **Istirahat di POSKESTREN**: Mulai episode observasi, jadwalkan pemantauan berkala per 2–4 jam.
   - **Rujukan ke RS/Faskes Mitra**: Buat pengajuan rujukan, tunjuk pendamping resmi, catat waktu keberangkatan dan serah terima faskes.
4. **Pemberian Obat ke Pasien**:
   - Verifikasi alergi pasien sebelum menyerahkan obat.
   - Pilih batch obat aktif dan konfirmasi pemberian; sistem akan memotong stok secara atomik.

---

## 3. Akhir Hari (*End of Day*) — Pukul 21:00 - 22:00 WIB

1. **Rekonsiliasi Kunjungan Medis**:
   - Pastikan seluruh kunjungan hari ini memiliki status akhir (selesai pulang, observasi, atau dirujuk). Tidak boleh ada kunjungan berstatus *in_progress* tanpa penanggung jawab shift.
2. **Serah Terima Shift Jaga (*Handover*)**:
   - Untuk santri yang masih diobservasi malam hari, buat lembar *shift handover* ke petugas jaga malam dan minta konfirmasi persetujuan penerima (*acknowledge*).
3. **Handoff Operasional ke Asrama**:
   - Kirimkan ringkasan handoff kepulangan santri ke pembina asrama terkait pembatasan aktivitas (misal: istirahat 2 hari di kamar asrama).
4. **Pengecekan Cadangan Data (*Backup Verification*)**:
   - Konfirmasi proses backup database otomatis malam hari berjalan sukses dengan ukuran non-zero.
