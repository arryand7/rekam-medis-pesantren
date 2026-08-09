---
id: DOC-FEATURE-TEST-MATRIX
title: "Feature Test Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-09
---

# Feature Test Matrix

| Area | Happy path | Validation | Authorization | State conflict / Concurrency | Audit |
|---|:---:|:---:|:---:|:---:|:---:|
| Identity & Access | ✓ | ✓ | ✓ | ✓ | ✓ |
| Gate Sync Dry-Run | ✓ | ✓ | ✓ | ✓ | ✓ |
| Health Profile | ✓ | ✓ | ✓ | ✓ | ✓ |
| Medical Visit Intake | ✓ | ✓ | ✓ | lockForUpdate | ✓ |
| Vital Signs | ✓ | ✓ | ✓ | finalized lock | ✓ |
| Clinical Assessment | ✓ | ✓ | ✓ | draft/finalize | ✓ |
| Observation Episodes | ✓ | ✓ | ✓ | active episode lock | ✓ |
| Shift Handover | ✓ | ✓ | ✓ | atomic transfer | ✓ |
| Pharmacy Inventory | ✓ | ✓ | ✓ | no negative stock | ✓ |
| Medication Admin | ✓ | ✓ | ✓ | reversal stock | ✓ |
| Consultation | ✓ | ✓ | ✓ | version checksum | ✓ |
| External Advice | ✓ | ✓ | ✓ | local decision | ✓ |
| Referral Creation | ✓ | ✓ | ✓ | lockForUpdate | ✓ |
| Referral Logistics | ✓ | ✓ | ✓ | primary companion | ✓ |
| Referral Handover | ✓ | ✓ | ✓ | idempotency key | ✓ |
| Destination Status | ✓ | ✓ | ✓ | handoff ≠ acceptance | ✓ |
| Referral Return | ✓ | ✓ | ✓ | one-return guard | ✓ |
| Return Review | ✓ | ✓ | ✓ | no auto-discharge | ✓ |
| Referral Document | ✓ | ✓ | ✓ | immutability / path traversal | download audit |
| MariaDB Concurrency | ✓ | ✓ | ✓ | 4 concurrent tests | ✓ |
| Theme & Dashboard | ✓ | enum | ✓ | - | - |
| Health Endpoint | ✓ | - | - | - | - |
