---
id: DOC-DOD
title: "Definition of Done"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Definition of Done

Sebuah task selesai bila:

- Requirement ID jelas.
- Acceptance criteria terpenuhi.
- Code mengikuti arsitektur.
- Validation server-side.
- Policy server-side.
- Transaction aman.
- Audit tersedia bila relevan.
- Error ditangani.
- Feature test lulus.
- Security test terkait lulus.
- UI responsif.
- Light/dark diuji.
- Accessibility dasar diuji.
- Migration aman.
- Dokumentasi diperbarui.
- Traceability diperbarui.
- Tidak ada secret/data nyata.
- `PROJECT-STATUS.md` dan `CHANGELOG.md` diperbarui.

## Tambahan untuk Gate sync

- Dry-run tersedia.
- Idempotency test lulus.
- Deactivation tidak menghapus history.
- Conflict report tersedia.
- Field ownership terdokumentasi.

## Tambahan untuk consultation

- Red flag guard diuji.
- Recipient dan attribution wajib.
- Summary version immutable.
- Transmission dan download diaudit.
- Data minimum direview.
