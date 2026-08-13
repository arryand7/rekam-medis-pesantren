---
id: DOC-UI-P5B-ROLE-ACTION-MATRIX
title: "Phase 5B Role-Action Matrix & Button Authorization"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B Role-Action Matrix & Button Authorization

Dokumen ini memetakan seluruh aksi operasional, formulir interaktif, dan tombol aksi klinis di Phase 5B terhadap hak akses (permission & role) berbasis server-side Policy dan Blade authorization.

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
BASELINE=v0.19.3 (PHASE-5A-FINAL-ACCEPTED)
TARGET=Phase 5B Clinical Workflow Continuity
```

---

## 1. Matrix Aksi per Role Klinis & Administratif

| Modul / Fitur | Aksi UI | Permission Kunci | Dokter Poskestren | Perawat / Bidan | Petugas Farmasi | Staff Administrasi | IT SysAdmin |
|---|---|---|:---:|:---:|:---:|:---:|:---:|
| **Kunjungan & Overview** | Lihat Workspace Kunjungan | `view-medical-visits` | ✅ | ✅ | ✅ | ✅ | ❌ |
| | Batalkan Kunjungan | `cancel-medical-visits` | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Panduan Next-Action Engine | `view-medical-visits` | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Ruang Observasi** | Lihat Direktori Observasi | `view-observations` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Mulai Episode Observasi | `manage-observations` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Tambah Monitoring Berkala | `record-observation-monitoring` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Handover Shift Observasi | `manage-observations` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Selesaikan Observasi | `manage-observations` | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Tele-Konsultasi** | Lihat Detail Konsultasi | `view-clinical-consultations` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Ajukan Konsultasi Baru | `create-clinical-consultations` | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Kirim Transmisi ke Mitra | `send-clinical-consultations` | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Catat Saran Medis Eksternal | `receive-external-clinical-advices` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Tetapkan Keputusan Lokal | `finalize-clinical-consultations` | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Rujukan Lanjutan / RS** | Lihat Timeline Rujukan | `view-referrals` | ✅ | ✅ | ❌ | ✅ | ❌ |
| | Buat Surat Rujukan | `create-referrals` | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Atur Transport & Sopir | `manage-referral-transports` | ✅ | ✅ | ❌ | ✅ | ❌ |
| | Catat Serah Terima Faskes | `record-referral-handovers` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Catat Pasien Kembali | `record-referral-returns` | ✅ | ✅ | ❌ | ✅ | ❌ |
| | Review Medis Kepulangan | `review-referral-returns` | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Disposisi & Kepulangan** | Buka Workspace Kepulangan | `view-visit-discharges` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Simpan Draf Resume Pulang | `prepare-visit-discharges` | ✅ | ✅ | ❌ | ❌ | ❌ |
| | Finalisasi Kepulangan | `finalize-visit-discharges` | ✅ | ❌ | ❌ | ❌ | ❌ |
| | Buat Handoff Pengurus | `manage-operational-handoffs` | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Farmasi & Stok** | Lihat Stok & Batch Obat | `view-pharmacy-inventory` | ✅ | ❌ | ✅ | ❌ | ❌ |
| | Catat Penerimaan Batch | `manage-pharmacy-inventory` | ❌ | ❌ | ✅ | ❌ | ❌ |
| | Buat Penyesuaian Stok | `manage-pharmacy-inventory` | ❌ | ❌ | ✅ | ❌ | ❌ |

---

## 2. Prinsip Non-Bypass & Security Invariant

1. **Server-Side Enforcement First**: Semua aksi tidak hanya disembunyikan di Blade UI melalui `@can` / Policy check, tetapi dijamin oleh `Gate::authorize()` dan Form Request Authorization pada controller endpoint.
2. **Read-Only State Locking**: Catatan episode observasi yang berstatus `completed`, konsultasi berstatus `completed`, atau disposisi berstatus `finalized` secara otomatis mengunci formulir mutasi di server.
3. **Minimum Necessary Privacy**: View pengurus asrama / pembina santri pada lembar handoff operasional tidak memaparkan diagnosis medis, catatan SOAP, atau alergi klinis sensitif.
