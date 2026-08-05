---
id: DOC-API-CONVENTIONS
title: "Konvensi API"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Konvensi API

- Prefix `/api/v1`.
- JSON UTF-8.
- Resource nouns plural.
- Server-generated IDs dan timestamps.
- Validation error konsisten.
- Pagination cursor atau page sesuai kebutuhan.
- Filter dan sort allowlist.
- Idempotency key untuk create integration kritis.
- Correlation ID.
- Versioning eksplisit.
- Jangan mengirim field sensitif hanya karena model memilikinya.
- API Resource/DTO untuk response.
- ETag/optimistic locking dipertimbangkan untuk record medis.
