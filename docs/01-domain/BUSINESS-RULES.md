---
id: DOC-BUSINESS-RULES
title: "Aturan Bisnis"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Aturan Bisnis

## Penerimaan dan keberadaan

### BR-001 — Santri sakit wajib ke POSKESTREN
Santri yang diketahui sakit tidak boleh tetap berada di asrama tanpa pemeriksaan dan pengawasan.

### BR-002 — Setiap kedatangan dicatat
Setiap santri yang diterima POSKESTREN harus memiliki kunjungan, termasuk keluhan ringan.

### BR-003 — Identitas santri wajib valid
Kunjungan tidak boleh dikaitkan ke identitas bebas yang tidak dapat ditelusuri, kecuali mode darurat yang kemudian wajib direkonsiliasi.

### BR-004 — Kunjungan aktif ganda dikendalikan
Sistem harus mencegah atau meminta keputusan eksplisit bila santri sudah memiliki kunjungan aktif.

### BR-005 — Petugas penerima tercatat
Setiap kunjungan harus mencatat pengguna yang menerima atau membuatnya.

## Pemeriksaan

### BR-006 — Keluhan utama wajib
Kunjungan tidak dapat masuk tahap assessment tanpa keluhan utama.

### BR-007 — Data vital mencatat waktu dan petugas
Setiap pengukuran tanda vital harus menyimpan waktu, petugas, nilai, satuan, dan catatan bila ada.

### BR-008 — Nilai tidak wajar harus dikonfirmasi
Sistem memberikan validasi rentang dan meminta konfirmasi, tetapi tidak menggantikan penilaian petugas.

### BR-009 — Assessment memiliki penanggung jawab
Assessment harus terkait dengan petugas yang berwenang.

### BR-010 — Disposisi wajib
Kunjungan yang selesai assessment harus memiliki disposisi yang valid.

## Observasi

### BR-011 — Observasi memiliki waktu mulai
Santri tidak dapat berstatus observasi tanpa waktu mulai dan alasan.

### BR-012 — Observasi memiliki monitoring
Frekuensi monitoring ditentukan SOP; setiap monitoring mencatat waktu dan petugas.

### BR-013 — Observasi harus ditutup
Observasi berakhir dengan pemulangan, rujukan, atau perpindahan penanganan.

## Obat

### BR-014 — Alergi diperiksa sebelum obat
Sistem harus menampilkan peringatan alergi yang relevan sebelum pemberian obat.

### BR-015 — Pemberian obat dapat ditelusuri
Catat obat, dosis, satuan, rute, waktu, alasan, petugas, dan referensi order bila digunakan.

### BR-016 — Stok berubah berdasarkan pemberian tervalidasi
Pengurangan stok dilakukan server-side dan atomik setelah pemberian dikonfirmasi.

### BR-017 — Pembatalan tidak menghapus jejak
Kesalahan pemberian atau pencatatan diperbaiki melalui pembatalan/addendum dengan alasan.

## Rujukan

### BR-018 — Rujukan memiliki alasan
Rujukan wajib mencatat alasan klinis/operasional sesuai SOP.

### BR-019 — Tujuan dan pendamping dicatat
Rujukan harus mencatat fasilitas tujuan dan pendamping, kecuali keadaan darurat yang kemudian dilengkapi.

### BR-020 — Status rujukan ditindaklanjuti
Rujukan tetap aktif sampai ada hasil atau tindak lanjut.

## Rekam medis dan keamanan

### BR-021 — Catatan final tidak di-hard-delete
Catatan medis yang telah disahkan tidak boleh dihapus permanen melalui alur normal.

### BR-022 — Koreksi memakai addendum atau versioning
Koreksi harus mempertahankan nilai sebelumnya, alasan, waktu, dan pelaku.

### BR-023 — Akses mengikuti need-to-know
Pengguna hanya melihat data yang diperlukan untuk tugasnya.

### BR-024 — Setiap mutasi medis diaudit
Create, update, finalize, cancel, download, dan akses sensitif tertentu harus tercatat.

### BR-025 — Waktu server adalah waktu resmi
Client tidak boleh menentukan timestamp resmi kejadian medis.

### BR-026 — Identitas pelaku tidak boleh berasal dari payload
Pelaku diambil dari sesi/token yang terautentikasi.

### BR-027 — Laporan manajemen mengutamakan agregasi
Detail individu hanya tersedia jika ada kewenangan dan tujuan yang sah.

## Integrasi

### BR-028 — Identitas utama berasal dari sistem sumber
Data identitas santri tidak diedit sembarangan di aplikasi kesehatan.

### BR-029 — Integrasi gagal tidak boleh merusak transaksi utama
Notifikasi dan sinkronisasi non-kritis menggunakan retry/idempotency.

### BR-030 — Status sakit yang dibagikan dibatasi
Integrasi Absensi hanya menerima data yang diperlukan, bukan seluruh rekam medis.

## Identitas, Gate, dan kelayakan pasien

### BR-031 — Gate adalah sumber kebenaran identitas
Field identitas dan tipe pengguna yang ditetapkan authoritative hanya dapat diperbarui melalui sinkronisasi Gate.

### BR-032 — Sinkronisasi harus idempotent
Payload atau event yang sama tidak boleh membuat person/user/patient duplikat.

### BR-033 — Sinkronisasi memiliki reconciliation report
Data missing, duplicate, conflict, stale, dan invalid harus dilaporkan tanpa overwrite diam-diam.

### BR-034 — Identifier internal tidak bergantung pada NIS/NIP
NIS, NIP, email, dan username dapat berubah; relasi internal menggunakan ID stabil.

### BR-035 — Deaktivasi tidak menghapus riwayat
User yang dinonaktifkan tetap mempertahankan person, patient profile, dan rekam kesehatan.

### BR-036 — Semua person manusia eligible sebagai pasien
Santri, guru, staf, pengasuh, dan tipe pengguna manusia lain dapat memiliki patient profile.

### BR-037 — Permission admin tidak membatalkan kelayakan pasien
Pengguna manusia yang menjadi admin tetap dapat dilayani sebagai pasien.

### BR-038 — Akun teknis tidak menjadi pasien
Service account, bot, dan akun administratif murni tidak memiliki patient profile.

### BR-039 — Perubahan tipe pengguna tidak memecah riwayat
Perubahan student menjadi alumni/staff atau perubahan organisasi tetap merujuk person dan patient yang sama bila identitas Gate sama.

## Konsultasi klinis jarak jauh

### BR-040 — Konsultasi berasal dari assessment terstruktur
Ringkasan konsultasi harus terkait dengan visit, author, timestamp, keluhan, data relevan, dan pertanyaan konsultasi.

### BR-041 — Red flag tidak menunggu konsultasi
Jika SOP menetapkan rujukan segera, konsultasi jarak jauh tidak boleh menunda keberangkatan pasien.

### BR-042 — Penerima konsultasi terverifikasi
Fasilitas, tenaga kesehatan, dan kanal yang digunakan harus dapat diidentifikasi.

### BR-043 — Data konsultasi mengikuti minimum necessary
Hanya data yang diperlukan untuk tujuan konsultasi yang dibagikan.

### BR-044 — Respons eksternal memiliki atribusi
Nama, peran/profesi, fasilitas, waktu respons, kanal, dan isi respons harus disimpan.

### BR-045 — Respons eksternal tidak menyamar sebagai assessment lokal
Saran eksternal disimpan sebagai external clinical advice dan dapat diadopsi oleh petugas lokal melalui keputusan yang terpisah.

### BR-046 — Pengiriman dan akses konsultasi diaudit
Pembuatan, finalisasi, pengiriman, download, respons, dan pembatalan harus memiliki audit trail.

### BR-047 — Ringkasan final tidak diedit langsung
Perubahan setelah pengiriman menggunakan revisi atau addendum yang dapat ditelusuri.
