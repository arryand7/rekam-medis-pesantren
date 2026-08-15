---
id: DOC-PHASE-5D0-RBAC-TEST-MATRIX
title: "Phase 5D0 RBAC Test Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D0 RBAC Test Matrix

| Skenario | Test | Status |
|---|---|---|
| CRUD role dan user administration | `RbacAdministrationTest` | PASS |
| Direct permission/effective source | `RbacAdministrationTest` | PASS |
| Non-admin direct route denial | `RbacPrivilegeEscalationTest` | PASS |
| Protected role/permission assignment | `RbacPrivilegeEscalationTest` | PASS |
| Self-escalation dan last super-admin | `RbacPrivilegeEscalationTest` | PASS |
| Role-aware sidebar | `RbacMenuVisibilityTest` | PASS |
| Seeder idempotency/non-destructive | `SeederSafetyTest` | PASS |

Targeted combined run pada 2026-08-15 termasuk security/outbox/pre-staging: 33 test / 175 assertion PASS. RBAC-only final rerun: 20 test / 90 assertion PASS.
