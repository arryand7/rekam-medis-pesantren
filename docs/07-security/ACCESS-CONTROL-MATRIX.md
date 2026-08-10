---
id: DOC-ACCESS-MATRIX
title: "Access Control Matrix"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Access Control Matrix

Seluruh rute operasional & medis wajib dilindungi oleh `Route::middleware('auth')`. Otorisasi lanjutan ditegakkan via Policy dan `Gate::before()` granular.


Legenda: F = penuh, T = terbatas, A = agregat, - = tidak boleh.

| Resource/Action | Health Professional | Assistant | Pharmacy | Dorm Supervisor | Homeroom | Management | Admin |
|---|---:|---:|---:|---:|---:|---:|---:|
| Profil kesehatan penuh | F | T | T | - | - | - | T |
| Buat kunjungan | F | F | - | - | - | - | T |
| Assessment | F | T sesuai kewenangan | - | - | - | - | - |
| Diagnosis | F sesuai kewenangan | - | - | - | - | A | - |
| Observasi | F | T | - | T operasional | - | A | - |
| Pemberian obat | F sesuai izin | T sesuai izin | T stok | - | - | A | - |
| Kelola stok | T | - | F | - | - | A | T |
| Rujukan | F sesuai izin | T | - | T operasional | T status | A | - |
| Status sakit/izin | F | F | - | T | T | A | T |
| Laporan individu | F | T | T | - | - | T khusus | - |
| Laporan agregat | T | - | T | T | T | F | T |
| Audit log | T diri/sendiri | - | - | - | - | T | F teknis |
| Role/permission | - | - | - | - | - | T approval | F |

Matrix final wajib dipetakan ke Policy dan permission granular.

| Gate sync dry-run | - | - | - | - | - | A | F |
| Gate sync apply | - | - | - | - | - | approval | F |
| Consultation create | F sesuai izin | T | - | - | - | A | - |
| Consultation send | F khusus | - | - | - | - | A | - |
| External advice | F | T | - | - | - | A | - |

## Aturan lintas role

Seseorang dapat menjadi actor dan patient pada waktu berbeda. Authorization harus memblokir self-access yang tidak memiliki tujuan, kecuali fitur patient portal secara eksplisit mengizinkannya.
