---
id: DOC-FEATURE-TEST-MATRIX
title: "Feature Test Matrix"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-09
---

# Feature Test Matrix

| Area | Happy path | Validation | Authorization | State conflict / Concurrency | Audit |
|---|:---:|:---:|:---:|:---:|:---:|
| Identity & Access | ✓ | ✓ | ✓ | ✓ | ✓ |
| Gate Sync Dry-Run | ✓ | ✓ | ✓ | ✓ | ✓ |
| Gate SSO Authentication | ✓ | ✓ | state/nonce | CSRF/replay | ✓ |
| Gate Application Entitlement | ✓ | ✓ | ✓ | admin ≠ clinical | ✓ |
| Gate Identity Projection | ✓ | ✓ | ✓ | lockForUpdate | ✓ |
| Gate Sync Apply | ✓ | ✓ | ✓ | idempotent | ✓ |
| Gate MariaDB Sync Concurrency | ✓ | ✓ | ✓ | deterministic single Person | ✓ |
| Gate Reconciliation | ✓ | ✓ | ✓ | approve/reject | ✓ |
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
| Discharge Readiness | ✓ | ✓ | ✓ | blockers & warnings | - |
| Visit Discharge Closure | ✓ | ✓ | ✓ | atomic visit transition | ✓ |
| Follow-Up Planning | ✓ | ✓ | ✓ | manual completion | ✓ |
| Activity Restrictions | ✓ | ✓ | ✓ | duration & rules | ✓ |
| Operational Handoffs | ✓ | ✓ | ✓ | minimum-necessary privacy | ✓ |
| Private Discharge Doc | ✓ | ✓ | ✓ | immutability / path traversal | download audit |
| Theme & Dashboard | ✓ | enum | ✓ | - | - |
| Health Liveness & Readiness Probes | ✓ | no secret leaks | ✓ | DB/Cache/Storage | probe |
| Patient Number Collision Hardening | ✓ | 1000 items | ✓ | retry & catch 1062 | ✓ |
| Attendance Sandbox Integration | ✓ | forbidden keys guard | ✓ | supersede/revoke | probe |
| End-to-End Clinical & Handoff UAT | ✓ | Scenarios A-E | ✓ | superseding events | ✓ |
| Outbox Failure & Dead-Letter | ✓ | backoff policy | permission retry | dead-letter transition | ✓ |
| Role Matrix & Privacy Isolation | ✓ | separation | policy 403 checks | no medical leakage | ✓ |
| Production Cutover Canary & Integrity | ✓ | 6 steps canary | ✓ | 0 duplicates/negative | ✓ |
