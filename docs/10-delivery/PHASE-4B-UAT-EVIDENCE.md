---
id: DOC-PHASE-4B-UAT-EVIDENCE
title: "Phase 4B End-to-End UAT Evidence & Scenario Logs"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4B End-to-End UAT Evidence & Scenario Logs

## 1. Ringkasan Eksekusi Skenario UAT

| Skenario | Deskripsi Alur | Hasil Pengujian | Validasi Privasi Data | Audit Trail |
|---|---|:---:|:---:|:---:|
| **Skenario A** | Kunjungan Sakit → Pengkajian Dokter → Kepulangan Istirahat di Asrama (3 Hari) → Pembatasan Aktivitas → Notifikasi Pembina Asrama → Outbox → Sandbox Absensi | ✅ PASS | Zero clinical payload (diagnosa, keluhan, obat tidak terkirim) | Rantai audit lengkap (`medical_visit`, `assessment`, `discharge`, `handoff`, `outbox`) |
| **Skenario B** | Observasi Poskestren 6 Jam → Pemantauan Berkala → Selesai Membaik → Kepulangan Kembali Beraktivitas Penuh → Update Absensi Sandbox | ✅ PASS | Payload `disposition_type: return_to_activity` terkirim tanpa data klinis | Audit observasi dan outbox tercatat |
| **Skenario C** | Kunjungan Gawat Darurat → Rujukan ke RS Mitra → Pasien Kembali → Review Catatan RS oleh Dokter Lokal → Kepulangan | ✅ PASS | Data resume RS disimpan lokal, tidak bocor ke outbox eksternal | Audit rujukan dan return review tercatat |
| **Skenario D** | Amandemen Kepulangan: Penyesuaian Istirahat Menjadi Aktivitas Ringan → Superseding Outbox Event | ✅ PASS | Event outbox versi 2 mereferensikan `supersedes_event_id` event 1 | Audit amandemen versi tercatat |
| **Skenario E** | Deaktivasi Pengguna di Gate IdP → Revalidasi Sesi POSKESTREN → Akses Ditolak | ✅ PASS | Force logout, seluruh rekam medis dan data pasien tetap utuh | Audit security event tercatat |

## 2. Bukti Pengujian Outbox Failure, Retry & Dead-Letter

| Kondisi Kegagalan | Respon Sistem | Status Outbox | Tindakan Recovery |
|---|---|---|---|
| HTTP 503 / Timeout upstream Sandbox | Dicatat di `integration_delivery_attempts`, backoff eksponensial dihitung | `failed` → `dead_letter` (setelah 5 kali percobaan) | Admin berizin menekan Retry Manual (`POST /integration/outbox/{id}/retry`) → Status kembali `pending` |
| Upstream Pulih | Pengiriman berhasil pada percobaan berikutnya | `acknowledged` | Status final dengan external reference ID |
| Upaya Retry Tanpa Izin | Ditolak oleh server dengan HTTP 403 Forbidden | `dead_letter` (tidak berubah) | Audit log `unauthorized_retry_blocked` |

## 3. Bukti Verifikasi Pemisahan Peran & Privasi (Role Matrix UAT)

| Peran yang Diuji | Akses Modul yang Diizinkan | Modul yang Wajib Diblokir (HTTP 403) | Status |
|---|---|---|:---:|
| **Administrator Teknis** | Sinkronisasi Gate, Rekonsiliasi, Pengaturan Sistem | Dashboard Klinis, Riwayat Rekam Medis Pasien | ✅ PASS |
| **Pembina Asrama** | Dashboard Operasional, Penerimaan Serah Terima (Handoff) | Dashboard Klinis, Detail Diagnosa Medis | ✅ PASS |
| **Wali Kelas** | Dashboard Operasional, Rekomendasi Dispensasi Kelas | Dashboard Klinis, Stok Obat Farmasi | ✅ PASS |
| **Tenaga Medis (Dokter)** | Dashboard Klinis, Pengkajian Medis, Resep, Kepulangan | Akses administratif sistem murni dibatasi | ✅ PASS |
| **Manajemen** | Dashboard Statistik Agregat, Laporan Kunjungan | Rekam Medis Individual Santri | ✅ PASS |
