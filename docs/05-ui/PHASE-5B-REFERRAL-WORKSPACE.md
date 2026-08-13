---
id: DOC-UI-P5B-REFERRAL-WORKSPACE
title: "Phase 5B Referral Timeline & Action Flow Specification"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B Referral Timeline & Action Flow Specification

Dokumen ini mendeskripsikan implementasi alur rujukan santri ke fasilitas kesehatan lanjutan (Puskesmas / Rumah Sakit), tracking status perjalanan, serah terima, kepulangan rujukan, dan telaah medis (*return review*).

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
STAGES_COUNT=7 Stages (Prepared -> Transport -> Arrived -> Handover -> External Care -> Returned -> Reviewed)
```

---

## 1. 7-Stage Visual Lifecycle Stepper

Tampilan `referrals.show` dilengkapi horizontal stepper dengan penanda visual:
1. **1. Disiapkan (`prepared`)**: Pembuatan surat rujukan dan diagnosis rujukan.
2. **2. Berangkat (`in_transit`)**: Penugasan kendaraan (ambulans/mobil), sopir, dan pendamping.
3. **3. Tiba di Faskes (`arrived_at_destination`)**: Konfirmasi kedatangan di IGD/Poli RS mitra.
4. **4. Serah Terima (`handover_completed`)**: Berita acara serah terima klinis dengan tenaga IGD penerima.
5. **5. Pelayanan Faskes (`external_care_in_progress`)**: Masa tindakan/pemeriksaan lanjutan di RS.
6. **6. Kembali (`return_recorded`)**: Santri tiba kembali di lingkungan pesantren.
7. **7. Selesai Ditelaah (`review_completed`)**: Dokter Poskestren melakukan telaah medis atas hasil RS.

---

## 2. Formulir & Interaksi Operasional

### A. Pengaturan Transport & Pendamping
- Modal input data jenis armada (`ambulance`, `pesantren_car`, `private_vehicle`), nomor plat kendaraan, nama sopir, nomor kontak, serta daftar pendamping santri (ustadz/pembina/perawat).

### B. Serah Terima di Faskes (Clinical Handover)
- Pencatatan nama tenaga medis penerima di RS tujuan, jabatan/profesi, ringkasan serah terima kondisi pasien, dan waktu serah terima.

### C. Pencatatan Pasien Kembali (Referral Return)
- Form pencatatan kepulangan santri membawa dokumen resume medis luar, diagnosa luar, instruksi obat lanjutan, dan tanggal rencana kontrol ulang di RS.

### D. Telaah Medis Kepulangan (Return Review)
- Dokter Poskestren meninjau kelanjutan terapi, mencocokkan obat luar dengan stok poskestren, dan memutuskan apakah santri memerlukan observasi lanjutan atau dapat kembali ke asrama.
