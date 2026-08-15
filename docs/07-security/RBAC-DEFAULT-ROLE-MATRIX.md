---
id: DOC-RBAC-DEFAULT-ROLE-MATRIX
title: "RBAC Default Role Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# RBAC Default Role Matrix

| Role | Batas default |
|---|---|
| `super_admin` | seluruh permission; protected |
| `admin` | administrasi identitas/RBAC, Gate/reconciliation, audit, settings, partner, management/operational reporting; protected |
| `petugas_kesehatan` | workflow klinis, observasi, obat klinis, konsultasi, rujukan, discharge dan clinical reports |
| `farmasi` | master/inventory/ledger, medication fulfillment/administration terkait, pharmacy dashboard/report |
| `pengasuh_asrama` | operational handoff/restriction/notification minimum necessary |
| `manajemen` | management dashboard dan health reports agregat/read-only |

Seeder hanya menambahkan permission baseline (`syncWithoutDetaching`) agar exception lokal yang sah tidak hilang. Matriks detail per permission tetap mengikuti `DatabaseSeeder` dan `ACCESS-CONTROL-MATRIX.md`.
