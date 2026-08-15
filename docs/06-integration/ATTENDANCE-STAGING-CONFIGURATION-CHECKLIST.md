---
id: DOC-ATTENDANCE-STAGING-CHECKLIST
title: "Attendance Staging Configuration Checklist"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Attendance Staging Configuration Checklist

- [ ] Endpoint sandbox, API key, TLS, IP allowlist dan timeout dikonfirmasi.
- [ ] Driver tetap `fake` dan integration disabled sampai approval.
- [ ] Payload hanya berisi identity mapping dan status operasional minimum necessary; diagnosis, SOAP, alergi, obat dan dokumen medis dilarang.
- [ ] Delivery memakai outbox; scheduler tunggal aktif dan retry/backoff sesuai kontrak.
- [ ] 2xx, 4xx, 5xx, timeout, duplicate/idempotency dan dead-letter diuji.
- [ ] Response body dan credential tidak tersimpan di log atau error record.
- [ ] Manual retry hanya oleh role berizin dan menghasilkan audit.
- [ ] Identity conflict tidak diselesaikan dengan overwrite diam-diam.
- [ ] Reconciliation report dan rollback integration tersedia.

Phase 5D memvalidasi fake/local flow saja; panggilan sandbox nyata berstatus **[PERLU DIKONFIRMASI]**.
