---
id: DOC-API-ERRORS
title: "Format Error API"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Format Error API

```json
{
  "error": {
    "code": "VISIT_ALREADY_ACTIVE",
    "message": "Santri masih memiliki kunjungan aktif.",
    "details": {},
    "correlation_id": "..."
  }
}
```

## HTTP status

- 400 request tidak dapat diproses secara umum.
- 401 belum terautentikasi.
- 403 tidak berwenang.
- 404 resource tidak ditemukan atau disamarkan.
- 409 konflik state/concurrency.
- 422 validation.
- 429 rate limit.
- 500 error internal tanpa detail sensitif.

## Rule

Pesan pengguna jelas, tetapi tidak mengungkap schema, stack trace, credential, atau data pasien lain.
