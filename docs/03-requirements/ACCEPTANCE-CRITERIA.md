---
id: DOC-ACCEPTANCE
title: "Acceptance Criteria"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Acceptance Criteria

## AC-VISIT-001
Given petugas terautentikasi, when membuat kunjungan dengan data valid, then sistem menyimpan waktu server, actor, dan audit.

## AC-VISIT-002
Given santri memiliki kunjungan aktif, when petugas membuat kunjungan baru, then sistem menolak atau meminta override dengan permission dan alasan.

## AC-ASSESS-001
Given assessment telah difinalisasi, when pengguna mencoba edit langsung, then sistem menolak dan menawarkan addendum bila diizinkan.

## AC-OBS-001
Given observasi aktif, when petugas menutup kunjungan, then sistem menolak sampai observasi memiliki outcome.

## AC-MED-001
Given alergi relevan tercatat, when obat dipilih, then sistem menampilkan peringatan yang harus diakui petugas.

## AC-MED-002
Given stok tidak cukup, when pemberian dikonfirmasi, then transaksi gagal tanpa membuat administration parsial.

## AC-REF-001
Given rujukan dibuat, when disimpan, then alasan, tujuan, urgensi, petugas, dan status tercatat.

## AC-AUTH-001
Given pengguna tanpa permission, when mengakses endpoint melalui URL langsung, then server mengembalikan 403.

## AC-THEME-001
Given pengguna memilih dark, when reload halaman, then tema dark diterapkan sebelum konten terlihat.

## AC-AUDIT-001
Given catatan medis diubah, when transaksi sukses, then audit berisi actor, action, target, waktu, dan perubahan.

## AC-GATE-001
Given payload Gate yang sama dikirim dua kali, when sync dijalankan, then tidak ada person/user/patient duplikat.

## AC-GATE-002
Given user dinonaktifkan di Gate, when sync selesai, then login diblokir tetapi riwayat kesehatan tetap tersedia untuk pengguna berwenang.

## AC-PATIENT-001
Given guru memiliki permission admin, when petugas membuat kunjungan untuk guru tersebut, then sistem tetap menemukan patient profile yang sama.

## AC-CONSULT-001
Given visit memiliki red flag, when consultation dibuat, then sistem memperingatkan dan tidak mengizinkan consultation menahan proses rujukan.

## AC-CONSULT-002
Given summary telah dikirim, when isi perlu diperbaiki, then sistem membuat revisi baru dan mempertahankan versi lama.

## AC-CONSULT-003
Given respons eksternal dicatat, then nama/fasilitas/waktu/kanal wajib tersedia atau respons ditandai unverified.
