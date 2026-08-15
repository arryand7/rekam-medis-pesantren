---
id: DOC-RBAC-PERMISSION-CATALOG
title: "RBAC Permission Catalog"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# RBAC Permission Catalog

Sumber kebenaran executable katalog adalah array `$permissions` pada `DatabaseSeeder`. Phase 5D memverifikasi 123 permission tersimpan dan mengelompokkannya pada UI berdasarkan domain.

| Domain | Contoh capability |
|---|---|
| System/identity | manage users/roles/permissions, Gate sync/reconciliation, audit log |
| Patient/visit | patient profile, visits, vital signs, assessment, initial action |
| Observation | monitoring, handover, completion, observation audit |
| Pharmacy | inventory, stock receipt/adjust/reverse/transfer, order/administer/correct medication |
| Consultation/referral | partner, transmission, advice, local decision, referral lifecycle/document/return review |
| Discharge/operational | discharge/finalize/amend, follow-up, restriction, handoff, notification |
| Integration/reporting | attendance settings, outbox retry, dashboards, report view/export |

Permission dengan dampak privilege tinggi (`manage-users`, `manage-roles`, `manage-permissions`, Gate/system management, dan permission protected lain pada model) hanya dapat diberikan oleh super-admin. Penambahan permission baru wajib memperbarui seeder, role matrix, access-control matrix, UI grouping dan tests.
