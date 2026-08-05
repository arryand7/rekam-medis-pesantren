---
id: DOC-SECURITY-TESTS
title: "Security Tests"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Security Tests

- IDOR untuk setiap resource.
- Role escalation.
- Permission bypass melalui direct endpoint.
- Mass assignment.
- Client-supplied actor/timestamp/status.
- CSRF.
- Session fixation.
- Rate limit.
- File upload MIME spoofing dan traversal.
- Export tanpa permission.
- Audit tampering.
- Soft-deleted/void record exposure.
- Sensitive field serialization.
- Query/search data leakage.
- Signed integration replay.
- Stock race condition.
- Stored XSS pada catatan medis.
- Error response tidak bocor.

- Gate token scope dan audience.
- Sync replay/idempotency.
- Source payload tampering.
- Unauthorized sync apply.
- Person/patient enumeration.
- Admin role incorrectly excluding or exposing patient.
- Consultation recipient substitution.
- Expired secure link.
- External advice spoofing.
- Emergency workflow blocked by consultation.
