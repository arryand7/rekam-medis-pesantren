---
id: DOC-INTEGRATION-CONTRACTS
title: "Kontrak Integrasi"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Kontrak Integrasi

## Identity sync event

Field minimum:
- source,
- external_id,
- identifier,
- name,
- type,
- active,
- updated_at,
- version.

## Sick status event

Field minimum:
- event_id,
- student_external_id,
- operational_status,
- effective_from,
- effective_until,
- activity_restriction,
- source_visit_reference,
- occurred_at.

Diagnosis dan isi assessment tidak termasuk payload default.

## Reliability

- Idempotency key.
- Retry dengan backoff.
- Dead-letter/manual retry.
- Signature/authentication.
- Schema version.
- Correlation ID.
- Reconciliation endpoint/report.

## [PERLU DIKONFIRMASI]

Kontrak aktual Gate, SSS, Absensi, dan WhatsApp.

## Gate full user projection

Gunakan kontrak rinci pada `GATE-USER-SYNC-CONTRACT.md`.

## Clinical consultation exchange

Payload atau dokumen harus membawa:
- consultation ID;
- version;
- purpose;
- minimal patient identity;
- clinical summary;
- author;
- generated timestamp;
- checksum;
- recipient;
- expiry bila link-based.

Respons harus membawa clinician/facility attribution dan response timestamp.
