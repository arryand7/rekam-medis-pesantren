---
id: DOC-TEST-PHASE5C2-001
title: "Matriks Pengujian & Verifikasi Phase 5C2"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-14
---

# Matriks Pengujian & Verifikasi Phase 5C2

Dokumen ini mencatat matriks uji regresi semantik pelaporan farmasi, bukti mutual-exclusivity batch status, dan validasi tampilan UI snapshot.

## 1. Matriks Dataset Uji Regresi Batch

| Kode Batch | Parameter Batch | Status Expired | Status Near-Expiry | Status Normal | Status Depleted |
|---|---|---|---|---|---|
| **Batch A** | `expiry = H-1 (yesterday)`, `qty = 20 (> 0)` | **YES** | **NO** | **NO** | **NO** |
| **Batch B** | `expiry = H+5 (within warning window)`, `qty = 80 (> 0)` | **NO** | **YES** | **NO** | **NO** |
| **Batch C** | `expiry = H+45 (outside 30-day window)`, `qty = 100 (> 0)` | **NO** | **NO** | **YES** | **NO** |
| **Batch D** | `expiry = H-1 (yesterday)`, `qty = 0 (habis)` | **NO (Not Alert)** | **NO** | **NO** | **YES** |

---

## 2. Matriks Pengujian Fitur (`Phase5C2PharmacyReportingClosureTest.php`)

| No | Skenario Uji | Tujuan & Validasi | Hasil |
|---|---|---|---|
| 1 | `expired and near-expiry batches are strictly mutually exclusive across models, queries, and reports` | Memverifikasi bahwa Batch A, B, C, D diklasifikasikan secara saling lepas di level model (`isExpired`, `isNearExpiry`, scopes), Dashboard Farmasi KPI, Laporan Ringkasan Farmasi, dan Dashboard Manajemen (jumlah total = 4). | **PASS** |
| 2 | `pharmacy stock report view displays current snapshot semantics and excludes date pickers` | Memverifikasi tampilan UI laporan farmasi menampilkan subtitle snapshot real-time, input pencarian kata kunci obat/batch, dan menyembunyikan input `start_date` / `end_date`. | **PASS** |
| 3 | `pharmacy stock report supports keyword search filter on medicine name and batch number` | Memverifikasi fungsionalitas pencarian nama dagang obat dan nomor batch beserta tampilan empty state ketika kata kunci tidak ditemukan. | **PASS** |
| 4 | `pharmacy stock CSV export declares current snapshot metadata without fake date ranges` | Memverifikasi metadata CSV menyajikan judul `"Snapshot Stok Farmasi Saat Ini"` dan tidak menyertakan filter tanggal palsu. | **PASS** |

---

## 3. Ringkasan Eksekusi Pengujian Keseluruhan

```text
Targeted Phase 5C2 Suite:  4 tests / 40 assertions (100% PASS)
Combined Phase 5C Suites:  19 tests / 108 assertions (100% PASS)
Full Application Suite:    244 tests / 1043 assertions (100% PASS, 0 Regressions)
Duration:                  15.4 detik
```
