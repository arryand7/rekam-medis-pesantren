---
id: DOC-GATE-SYNC-CONTRACT
title: "Kontrak Sinkronisasi Pengguna Gate"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Kontrak Sinkronisasi Pengguna Gate

## Payload minimum

```json
{
  "id": "gate-stable-id",
  "username": "identifier",
  "name": "Nama Pengguna",
  "email": "user@example.sch.id",
  "phone": null,
  "user_type": "student",
  "active": true,
  "organization": {
    "class_id": null,
    "dorm_id": null,
    "unit_id": null
  },
  "photo_url": null,
  "updated_at": "2026-08-05T03:00:00Z",
  "version": "opaque-version"
}
```

## Field ownership

| Field | Source |
|---|---|
| Gate ID | Gate |
| Nama | Gate |
| Username/NIS/NIP | Gate |
| Email/phone | Gate bila disepakati |
| Tipe pengguna | Gate |
| Status aktif | Gate |
| Role klinis lokal | POSKESTREN/Gate ACL sesuai kontrak |
| Patient number | POSKESTREN |
| Health profile | POSKESTREN |
| Medical history | POSKESTREN |

## Requirements

- Pagination.
- Incremental cursor atau `updated_since`.
- Stable ID.
- Version/timestamp.
- Active/deactivated state.
- Authentication.
- Rate limit.
- Error per item.
- Idempotency.
- Optional webhook signature.

## Conflict policy

Nama/email berubah: update projection.  
ID collision: stop dan review.  
User missing from source: mark source_missing, jangan hapus.  
Unsupported user type: quarantine/review.  
Duplicate NIS/NIP: report conflict.
