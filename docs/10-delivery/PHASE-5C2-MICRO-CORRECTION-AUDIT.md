---
id: DOC-DELIVERY-PHASE5C2-001
title: "Phase 5C2 Micro-Correction & Audit"
status: finalized
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-14
---

# Phase 5C2 Micro-Correction & Audit

Dokumen ini mencatat audit perbaikan micro-closure semantik pelaporan farmasi dan eliminasi double-counting kategori kedaluwarsa batch obat.

## 1. Analisis Akar Masalah (Root Cause Analysis)

1. **Near-Expiry vs Expired Overlap**:
   - Sebelumnya pada `HealthReportService::getReportSummary('pharmacy_stock')`, kueri `near_expiry_batches` menggunakan klausa `where('expiry_date', '<=', $threshold)` tanpa batas bawah `where('expiry_date', '>=', $today)`. Akibatnya, batch yang telah kedaluwarsa kemarin (`expiry_date < today`) terhitung ganda (*double-counted*) ke dalam metrik `near_expiry`.
   - Pada `ManagementDashboardQuery`, kategori `active` dihitung dengan `where('expiry_date', '>', now())` yang mencakup batch *near-expiry*, sehingga jumlah kartu tidak saling lepas (*not mutually exclusive*).
2. **Semantik Laporan Stok Farmasi (Pharmacy Stock Report)**:
   - Modul `pharmacy_stock` hakikatnya adalah **Snapshot Stok Terkini (Current Inventory State)**, bukan buku besar mutasi historis (*historical ledger*).
   - Menampilkan input pemilih tanggal (`start_date`, `end_date`) pada UI laporan snapshot berpotensi menyesatkan pengguna (*confusing UX*), karena filter tanggal diabaikan oleh backend snapshot.

---

## 2. Resolusi Semantik & Implementasi Terstandarisasi

### A. Definisi Baku 4 Kategori Status Batch

| Kategori | Syarat Kueri SQL / Model Scope | Status Ketergantungan | Sifat |
|---|---|---|---|
| **Expired (Kedaluwarsa)** | `expiry_date < CURRENT_DATE AND current_quantity > 0` | `scopeExpired()` / `$batch->isExpired()` | Saling Lepas (*Mutually Exclusive*) |
| **Near-Expiry (Hampir Kedaluwarsa)** | `expiry_date >= CURRENT_DATE AND expiry_date <= threshold AND current_quantity > 0` | `scopeNearExpiry()` / `$batch->isNearExpiry()` | Saling Lepas (*Mutually Exclusive*) |
| **Normal / Safe (Aktif & Aman)** | `expiry_date > threshold AND current_quantity > 0` | `scopeNormal()` | Saling Lepas (*Mutually Exclusive*) |
| **Depleted (Habis)** | `current_quantity <= 0` | `scopeDepleted()` | Saling Lepas (*Mutually Exclusive*) |

> **Jaminan Konsistensi Matematis**:
> `Depleted + Expired + NearExpiry + Normal = Total Batches Aktif di Database` (100% konsisten tanpa double-counting).

### B. Resolusi UI & Ekspor Laporan Stok Farmasi

1. **Pemisahan Filter Form**: Pada halaman `reports/show?report_type=pharmacy_stock`, input tanggal (`start_date`, `end_date`) disembunyikan dan digantikan dengan input pencarian kata kunci (`search`) nama obat / nomor batch.
2. **Metadata Header CSV**: Judul laporan ekspor ditetapkan sebagai `"Snapshot Stok Farmasi Saat Ini"` dan tidak lagi mencantumkan metadata filter rentang tanggal semu.
3. **Status Kolom CSV & Web**: Kolom status pada tabel dan file CSV menampilkan klasifikasi akurat: `Kedaluwarsa`, `Hampir Kedaluwarsa`, `Aktif`, atau `Habis`.
