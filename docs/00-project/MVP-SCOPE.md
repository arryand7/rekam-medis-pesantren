---
id: DOC-MVP-SCOPE
title: "Ruang Lingkup MVP"
status: draft
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# Ruang Lingkup MVP

## Modul wajib MVP

1. Authentication dan role-based access.
2. Sinkronisasi data santri.
3. Profil kesehatan dasar.
4. Alergi dan kondisi medis penting.
5. Registrasi kunjungan.
6. Keluhan utama dan riwayat singkat.
7. Tanda vital.
8. Assessment awal.
9. Tindakan pertama.
10. Disposisi.
11. Observasi dan monitoring berkala.
12. Master obat dan stok sederhana.
13. Pemberian obat.
14. Rujukan.
15. Kembali dari rujukan.
16. Pemulangan dan instruksi lanjutan.
17. Dashboard operasional.
18. Riwayat rekam medis.
19. Audit log.
20. Laporan dasar.
21. Tema light, dark, system.

## Kriteria keberhasilan MVP

- Setiap santri sakit dapat ditelusuri dari masuk sampai selesai.
- Tidak ada kunjungan aktif tanpa status yang jelas.
- Pemberian obat dapat ditelusuri ke batch/stok bila fitur batch diaktifkan.
- Rujukan memiliki alasan, tujuan, pendamping, dan status tindak lanjut.
- Pengasuh dan wali kelas hanya melihat informasi operasional yang diizinkan.
- Semua perubahan medis penting tercatat pada audit log.
- Fitur utama dapat digunakan di ponsel dan desktop.
- Test kritis lulus.

## Ditunda ke fase berikutnya

- Portal wali.
- WhatsApp otomatis.
- Integrasi penuh Absensi.
- Analitik kesehatan lanjutan.
- Penyakit menular dan outbreak management khusus.
- Inventory farmasi tingkat lanjut.
- Mode offline.

## Tambahan capability MVP

22. Sinkronisasi detail pengguna dan tipe pengguna dari Gate.
23. Reconciliation report untuk data tidak cocok, duplikat, dan missing source.
24. Patient profile untuk semua pengguna manusia.
25. Clinical consultation summary.
26. Status konsultasi dan pencatatan respons eksternal.
27. Ekspor ringkasan konsultasi yang aman dan diaudit.

## Kriteria keberhasilan tambahan

- Perubahan identitas dari Gate dapat diterapkan tanpa mengubah riwayat medis.
- Akun yang dinonaktifkan tetap mempertahankan riwayat.
- Pengguna manusia yang juga admin tetap dapat dicatat sebagai pasien.
- Setiap respons eksternal memiliki identitas sumber dan timestamp.
- Konsultasi jarak jauh tidak dapat menutup red flag tanpa keputusan petugas berwenang.
