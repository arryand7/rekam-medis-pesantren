---
id: DOC-UI-P5B-PHARMACY-UX
title: "Phase 5B Pharmacy Operational UX Specification"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-12
---

# Phase 5B Pharmacy Operational UX Specification

Dokumen ini mendeskripsikan implementasi antarmuka pemantauan inventaris farmasi, batch obat, peringatan masa kedaluwarsa (*expiry tracking*), penerimaan stok, dan penyesuaian stok (*stock opname*).

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
STOCK_LOCATIONS=Internal Pharmacy / Poskestren Main Storage
```

---

## 1. Halaman Inventaris Batch (`pharmacy.inventory.index`)

### A. Indikator Status Kedaluwarsa Dinamis
Tabel inventaris batch secara otomatis menghitung selisih hari terhadap tanggal kedaluwarsa:
1. **Kedaluwarsa (`isExpired`)**:
   - Ditandai dengan badge merah menyala: `⚠️ Kedaluwarsa (DD Mon YYYY)`.
   - Batch tidak boleh didispensasikan untuk resep baru.
2. **Hampir Kedaluwarsa (`isNearExpiry` $\le 30$ hari)**:
   - Ditandai dengan badge kuning/amber: `⏳ Hampir Kedaluwarsa (DD Mon YYYY)`.
   - Membantu petugas farmasi menerapkan prinsip *FEFO (First Expired, First Out)*.
3. **Masa Simpan Normal**:
   - Ditandai dengan teks tanggal standar monospaced.

### B. Saldo & Lokasi Fisik
- Menampilkan lokasi penyimpanan obat (e.g. *Apotek Utama Poskestren*).
- Menampilkan jumlah saldo riil saat ini beserta satuan dasar (*tablet, botol, ampul, strip*).
- Menampilkan badge status operasional batch (`active`, `expired`, `depleted`, `quarantined`).

---

## 2. Aksi Operasional Farmasi

1. **Penerimaan Stok Baru (`pharmacy.receipt.create`)**:
   - Form pencatatan nomor faktur / PO, supplier, nomor batch baru, tanggal kedaluwarsa, dan jumlah unit masuk.
2. **Penyesuaian Stok (`pharmacy.adjustments.create`)**:
   - Form penyesuaian selisih stok fisik (*stock opname*) dengan kewajiban mengisi alasan (*kerusakan, selisih hitung, obat pecah*).
