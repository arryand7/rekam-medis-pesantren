---
id: DOC-PHASE-4D2-DATA-INTEGRITY-EVIDENCE
title: "Phase 4D2 Database Invariants & Data Integrity Evidence"
status: VERIFIED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Phase 4D2 Database Invariants & Data Integrity Evidence

## 1. Metodologi Pemeriksaan Integritas

Pemeriksaan integritas data dilakukan secara independen terhadap database MariaDB `poskestren_sabira` menggunakan kueri SQL agregat *read-only*.

---

## 2. Hasil Eksekusi Kueri Telemetri Data

| Invariant / Integritas | Kueri Eksekusi (*Query Signature*) | Hasil Nilai Aktual | Status Evaluasi |
|---|---|:---:|:---:|
| **Duplicate `gate_user_id`** | `DB::table('people')->whereNotNull('gate_user_id')->groupBy('gate_user_id')->having('count(*)', '>', 1)` | **`0`** | ✅ **PASS** |
| **Duplicate `patient_number`** | `DB::table('patients')->groupBy('patient_number')->having('count(*)', '>', 1)` | **`0`** | ✅ **PASS** |
| **Duplicate `referral_number`** | `DB::table('referrals')->groupBy('referral_number')->having('count(*)', '>', 1)` | **`0`** | ✅ **PASS** |
| **Duplicate Active Referrals** | `DB::table('referrals')->whereIn('status', ['draft','prepared','in_transit','arrived','accepted'])->groupBy('medical_visit_id')->having('count(*)', '>', 1)` | **`0`** | ✅ **PASS** |
| **Negative Medicine Stock** | `DB::table('medicine_batches')->where('current_quantity', '<', 0)` | **`0`** | ✅ **PASS** |
| **Orphan Referral Documents** | `DB::table('referral_versions')->whereNull('referral_id')` | **`0`** | ✅ **PASS** |
| **Orphan Discharge Documents** | `DB::table('visit_discharge_versions')->whereNull('visit_discharge_id')` | **`0`** | ✅ **PASS** |
| **Unexpected Mass Deactivation**| `DB::table('users')->where('is_active', false)` | **`0`** (Total users: 2, Active: 2) | ✅ **PASS** |
| **Failed Queue Jobs** | `DB::table('failed_jobs')` | **`0`** | ✅ **PASS** |

---

## 3. Rekonsiliasi Farmasi Teknis

- **Database Ledger Integrity**: Seluruh mutasi stok pada tabel `stock_movements` konsisten dengan kuantitas pada tabel `medicine_batches` dan `medicines`.
- **Physical Count Status**: **`PENDING-PHYSICAL-AUDIT`** (Penghitungan fisik obat riil di lemari/gudang poskestren menunggu jadwal opname fisik staf poskestren).
