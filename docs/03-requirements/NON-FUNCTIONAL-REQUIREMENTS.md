---
id: DOC-NONFUNCTIONAL-REQ
title: "Kebutuhan Non-Fungsional"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Kebutuhan Non-Fungsional

## Security

- **NFR-SEC-001** Semua akses data medis memerlukan authentication dan authorization.
- **NFR-SEC-002** Data sensitif dienkripsi saat transit.
- **NFR-SEC-003** Credential dan secret tidak disimpan di repositori.
- **NFR-SEC-004** Rate limit diterapkan pada authentication dan endpoint sensitif.
- **NFR-SEC-005** Session dapat dicabut.

## Privacy

- **NFR-PRIV-001** Data yang tampil mengikuti minimum necessary.
- **NFR-PRIV-002** Pengasuh/wali kelas tidak otomatis melihat diagnosis.
- **NFR-PRIV-003** Ekspor dan unduhan sensitif diaudit.

## Reliability

- **NFR-REL-001** Transaksi medis penting bersifat atomik.
- **NFR-REL-002** Side effect non-kritis menggunakan retry dan idempotency.
- **NFR-REL-003** Backup dan restore diuji berkala.
- **NFR-REL-004** Waktu aplikasi memakai `Asia/Jakarta`.

## Performance

- **NFR-PERF-001** Halaman operasional utama target p95 di bawah 2 detik pada jaringan internal normal.
- **NFR-PERF-002** Pencarian santri menggunakan index.
- **NFR-PERF-003** Daftar besar menggunakan pagination server-side.

## Usability

- **NFR-UX-001** Fitur utama responsif pada ponsel, tablet, dan desktop.
- **NFR-UX-002** Form mempertahankan data ketika validation gagal.
- **NFR-UX-003** Status penting memiliki teks dan ikon, bukan warna saja.
- **NFR-THEME-001** Mendukung light, dark, system tanpa flash tema.
- **NFR-PRINT-001** Dokumen cetak selalu menggunakan tema terang.

## Maintainability

- **NFR-MAINT-001** Business logic tidak ditempatkan di view.
- **NFR-MAINT-002** Requirement memiliki traceability ke code dan test.
- **NFR-MAINT-003** Perubahan arsitektur signifikan memerlukan ADR.
- **NFR-MAINT-004** CI menjalankan test dan quality check.

## Auditability

- **NFR-AUDIT-001** Audit log append-only.
- **NFR-AUDIT-002** Timestamp, actor, target, action, before/after, IP, user-agent, dan correlation ID disimpan sesuai kebutuhan.

## Identity synchronization

- **NFR-SYNC-001** Sinkronisasi bersifat idempotent.
- **NFR-SYNC-002** Batch gagal dapat dilanjutkan dari cursor/checkpoint.
- **NFR-SYNC-003** Field authoritative dan lokal didefinisikan eksplisit.
- **NFR-SYNC-004** Rekonsiliasi menyediakan evidence dan tidak melakukan merge otomatis berisiko.

## Remote consultation

- **NFR-CONSULT-001** Data consultation dienkripsi saat transit dan tersimpan private.
- **NFR-CONSULT-002** Kanal, penerima, download, dan response diaudit.
- **NFR-CONSULT-003** Consultation summary menggunakan minimum necessary.
- **NFR-CONSULT-004** Dokumen memiliki version, checksum, author, dan generated timestamp.
- **NFR-CONSULT-005** Kegagalan kanal tidak boleh menghapus draft atau mengubah visit menjadi selesai.
