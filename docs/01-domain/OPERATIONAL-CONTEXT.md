---
id: DOC-OPERATIONAL-CONTEXT
title: "Konteks Operasional POSKESTREN"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Konteks Operasional POSKESTREN

## Kondisi dasar

- Seluruh santri tinggal selama 24 jam di asrama.
- Santri yang diketahui sakit tidak boleh tetap berada di asrama.
- Santri wajib dibawa atau diarahkan ke POSKESTREN.
- Tim kesehatan mendata keluhan dan melakukan tindakan pertama.
- Tim kesehatan memutuskan apakah santri:
  - dapat kembali beraktivitas,
  - perlu istirahat atau observasi di POSKESTREN,
  - perlu dirujuk ke rumah sakit/fasilitas kesehatan,
  - atau membutuhkan penanganan darurat.

## Konsekuensi untuk sistem

- Sistem harus mendukung layanan lintas jam sekolah.
- Status keberadaan santri harus jelas.
- Harus ada petugas penanggung jawab.
- Setiap keputusan harus memiliki alasan dan waktu.
- Kunjungan yang belum selesai harus muncul pada dashboard operasional.
- Riwayat santri harus mudah ditemukan, tetapi aksesnya dibatasi.
- Informasi operasional dan informasi medis harus dipisahkan.

## Pertanyaan domain yang belum final

- Siapa yang boleh menyatakan santri sakit?
- Siapa yang wajib mengantar?
- Apa yang dilakukan saat POSKESTREN tanpa petugas?
- Bagaimana shift malam?
- Siapa yang berwenang memberi obat?
- Siapa yang berwenang membuat rujukan?
- Kapan wali wajib dihubungi?
- Bagaimana penanganan santri yang menolak pemeriksaan?
- Bagaimana kondisi penyakit menular?

## Subjek pelayanan yang diperluas

Aturan wajib berada di POSKESTREN ketika sakit berlaku khusus bagi santri berasrama. Namun sistem rekam kesehatan dapat melayani guru, staf, pengasuh, dan pengguna manusia lain yang disinkronkan dari Gate.

Workflow dapat berbeda berdasarkan `patient_type`, tetapi seluruh riwayat menggunakan patient record yang konsisten.

## Konsultasi tanpa pasien datang ke fasilitas eksternal

Setelah menerima keluhan dan melakukan assessment, tim kesehatan dapat menyusun ringkasan terstruktur untuk dikonsultasikan kepada Puskesmas atau rumah sakit. Tujuannya memperoleh pertimbangan awal dan menentukan tindak lanjut tanpa pasien harus langsung datang.

Konsultasi tersebut tidak boleh menunda rujukan langsung jika ditemukan red flag atau keadaan darurat.
