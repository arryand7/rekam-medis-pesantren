---
id: DOC-FEATURE-TEST-MATRIX
title: "Feature Test Matrix"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Feature Test Matrix

| Area | Happy path | Validation | Authorization | State conflict | Audit |
|---|---:|---:|---:|---:|---:|
| Login | ✓ | ✓ | ✓ | - | ✓ |
| Student sync | ✓ | ✓ | ✓ | ✓ | ✓ |
| Health profile | ✓ | ✓ | ✓ | ✓ | ✓ |
| Visit | ✓ | ✓ | ✓ | ✓ | ✓ |
| Vital signs | ✓ | ✓ | ✓ | ✓ | ✓ |
| Assessment | ✓ | ✓ | ✓ | ✓ | ✓ |
| Observation | ✓ | ✓ | ✓ | ✓ | ✓ |
| Medication | ✓ | ✓ | ✓ | ✓ | ✓ |
| Stock | ✓ | ✓ | ✓ | race test | ✓ |
| Referral | ✓ | ✓ | ✓ | ✓ | ✓ |
| Discharge | ✓ | ✓ | ✓ | ✓ | ✓ |
| Reports | ✓ | filter | ✓ | - | export |
| Theme | ✓ | enum | owner | - | optional |

| Gate sync | ✓ | schema | super admin | idempotency/conflict | ✓ |
| Person/patient | ✓ | eligibility | privacy | duplicate merge | ✓ |
| Consultation | ✓ | required/minimum | clinical permission | version/state | ✓ |
| External advice | ✓ | attribution | permission | unverified source | ✓ |
