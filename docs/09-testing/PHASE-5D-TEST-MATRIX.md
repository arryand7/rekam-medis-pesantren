---
id: DOC-PHASE-5D-TEST-MATRIX
title: "Phase 5D Test Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D Test Matrix

| Capability | Automated evidence | Status |
|---|---|---|
| RBAC administration/escalation/menu | `RbacAdministrationTest`, `RbacPrivilegeEscalationTest`, `RbacMenuVisibilityTest` | PASS |
| Seeder safety | `SeederSafetyTest` | PASS |
| Outbox command/retry/privacy | `IntegrationOutboxCommandTest`, integration failure tests | PASS |
| Gate SSO/sync preservation | Gate auth/sync feature and security suites | PASS lokal/fake |
| APP_DEBUG=false/log secret/health | `PreStagingSecurityTest` | PASS |
| Private referral/discharge files | document authorization feature tests | PASS |
| Multi-role privacy | role matrix/privacy UAT tests | PASS |
| Migration/cache/build/dependencies | command rehearsal | PASS |
| Backup/restore | isolated MariaDB + private storage rehearsal | PASS lokal |
| Real Gate/Attendance | staging checklist | MANUAL-STAGING |
| Browser responsive/theme | existing UI feature tests; in-app browser unavailable this session | MANUAL-REVERIFY |

Targeted Phase 5D run: 33 test / 175 assertion PASS. Entry full suite: 266 test / 1.168 assertion PASS. Final full suite: 277 test / 1.218 assertion PASS.
