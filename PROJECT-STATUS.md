---
id: DOC-PROJECT-STATUS
title: "Status Proyek"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Status Proyek

## Fase saat ini

**Phase 2D1 Completed — Pharmacy Inventory Foundation & Append-Only Stock Ledger**

## Perubahan & Fitur Selesai di Phase 2D1

- [x] **Phase 2C Closure Audit**: Laporan closure Phase 2C diterbitkan di [PHASE-2C-CLOSURE.md](file:///Users/ryand/Documents/LARAVEL/sabira/rekam-medis-ponpes/docs/10-delivery/PHASE-2C-CLOSURE.md) dengan status `PASSED`.
- [x] **Master Data Obat (Medicine Master)**:
  - Skema ULID `medicines` (`code`, `generic_name`, `brand_name`, `dosage_form`, `strength_text`, `base_unit`, `category`, `minimum_stock`, `is_active`).
- [x] **Master Lokasi Stok (Stock Locations)**:
  - Skema ULID `stock_locations` (`code`, `name`, `description`, `is_active`). Default: Ruang Apotek Utama Poskestren (`PHARMACY_MAIN`).
- [x] **Tracking Batch & Kedaluwarsa (Medicine Batches)**:
  - Skema ULID `medicine_batches` (`medicine_id`, `stock_location_id`, `batch_number`, `expiry_date`, `initial_quantity`, `current_quantity`, `status` [`active`, `depleted`, `expired`, `quarantined`, `recalled`, `entered_in_error`]).
- [x] **Append-Only Stock Ledger (`stock_movements`)**:
  - Skema ULID `stock_movements` (`movement_type` [`receipt`, `adjustment_in`, `adjustment_out`, `transfer_in`, `transfer_out`, `reversal`], `quantity`, `occurred_at`, `reason`, `idempotency_key`, `reverses_movement_id`).
  - **No Negative Stock Guard**: Menolak mutasi pengeluaran stok yang melebihi persediaan batch.
- [x] **Otorisasi Server-Side & Policy**:
  - Policy terpasang: `MedicinePolicy`, `MedicineBatchPolicy`, `StockMovementPolicy`, `StockLocationPolicy`.
- [x] **Pharmacy UI Shell**:
  - Halaman Master Obat (`/pharmacy/medicines`), Dashboard Stok & Batch (`/pharmacy/inventory`), Form Penerimaan Stok (`/pharmacy/receipt/create`), dan Form Penyesuaian Stok (`/pharmacy/adjustments/create`).

## Kemajuan Phase

- [x] **Phase 0 — Readiness & Foundation**: Selesai.
- [x] **Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync**: Selesai.
- [x] **Phase 2A — Patient Health Profile & Medical Visit Intake Foundation**: Selesai.
- [x] **Phase 2B — Vital Signs, Clinical Assessment, Initial Actions & Disposition**: Selesai.
- [x] **Phase 2C — POSKESTREN Observation, Periodic Monitoring & Shift Handover**: Selesai.
- [x] **Phase 2D1 — Pharmacy Inventory Foundation & Append-Only Stock Ledger**: Selesai.
- [ ] **Phase 2D2 / Phase 3 — Medication Order, Prescription & Patient Administration**: Menunggu persetujuan pengguna.

## Last verified

- Tanggal: 2026-08-05
- Test Suite: 33 tests, 107 assertions (100% Passed)
- Code Formatter: Pint Passed
- Static Analysis: PHPStan Level 5 Passed (0 errors)
- Route List: 44 routes terdaftar bersih
