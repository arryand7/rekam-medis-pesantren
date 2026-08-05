---
id: DOC-PROJECT-BRIEF
title: "Project Brief"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Project Brief

## Latar belakang

Seluruh santri tinggal selama 24 jam di lingkungan asrama. Ketika santri sakit, santri tidak boleh tetap berada di asrama, tetapi wajib berada di POSKESTREN agar mendapatkan pendataan, pemeriksaan, tindakan pertama, pemantauan, dan keputusan penanganan.

Pencatatan yang tersebar atau manual dapat menyebabkan riwayat tidak lengkap, informasi terlambat, pemberian obat sulit ditelusuri, keputusan rujukan tidak terdokumentasi, dan pihak sekolah tidak memiliki gambaran kesehatan santri yang akurat.

## Visi

Menyediakan sistem pelayanan kesehatan santri yang terpusat, aman, mudah digunakan, dapat diaudit, dan terintegrasi dengan ekosistem digital SABIRA.

## Tujuan

- Menyimpan profil kesehatan dan rekam medis santri.
- Mengelola episode kunjungan dari laporan sakit sampai selesai.
- Mendukung observasi di POSKESTREN.
- Mendokumentasikan pemberian obat.
- Mendukung proses rujukan dan tindak lanjut.
- Menyediakan status operasional tanpa membuka informasi medis melebihi kebutuhan.
- Menyediakan laporan manajemen yang agregat.
- Menyediakan traceability dari aturan bisnis sampai test melalui Graphify.

## Pengguna utama

- Petugas kesehatan.
- Kepala POSKESTREN.
- Admin POSKESTREN.
- Pengasuh asrama.
- Wali kelas.
- Manajemen sekolah/pesantren.
- Petugas farmasi.
- Super administrator.
- Santri dan wali pada fase lanjutan.

## Prinsip produk

1. Patient safety lebih penting daripada kenyamanan teknis.
2. Data minimal sesuai kebutuhan.
3. Least privilege.
4. Auditability.
5. Server-authoritative.
6. Mobile-first untuk aktivitas lapangan.
7. Dokumentasi dan test sebagai bagian produk.

## Perluasan subjek pasien

Sistem melayani semua manusia yang terdaftar melalui Gate dan memenuhi syarat sebagai pasien, termasuk:

- santri;
- guru;
- tenaga kependidikan;
- staf;
- pengasuh;
- jenis pengguna manusia lain yang ditetapkan Gate.

Akun teknis, service account, bot, dan akun administratif murni tidak menjadi pasien.

## Konsultasi klinis jarak jauh

Tim kesehatan dapat membuat ringkasan kasus untuk meminta pertimbangan Puskesmas atau rumah sakit tanpa pasien datang terlebih dahulu. Ringkasan ini mendukung keputusan lanjutan, tetapi tidak menggantikan rujukan langsung ketika terdapat kondisi darurat atau red flag.
