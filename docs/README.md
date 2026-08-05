---
id: DOC-DOCS-INDEX
title: "Indeks Dokumentasi"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Indeks Dokumentasi

Dokumentasi dibaca dalam urutan berikut:

1. `00-project` — tujuan, scope, MVP, istilah, stakeholder.
2. `01-domain` — konteks operasional, aturan bisnis, peran, perjalanan pasien.
3. `02-workflows` — alur per proses.
4. `03-requirements` — kebutuhan fungsional dan non-fungsional.
5. `04-architecture` — stack dan rancangan aplikasi.
6. `05-data` — model domain, schema, lifecycle, versioning.
7. `06-ui-ux` — konsep antarmuka dan tema.
8. `07-security` — privacy, authorization, audit, threat model.
9. `08-api` — konvensi dan kontrak integrasi.
10. `09-testing` — strategi, skenario, UAT.
11. `10-delivery` — roadmap, deployment, operasi.
12. `11-decisions` — Architecture Decision Records.
13. `12-graphify` — cara membangun knowledge graph dan handoff AI.

## Status dokumen

- `draft`: belum divalidasi stakeholder.
- `review`: sedang diperiksa.
- `approved`: telah disetujui.
- `active`: menjadi acuan kerja.
- `deprecated`: tidak lagi digunakan.

## Aturan perubahan

- Ubah `last_updated`.
- Catat perubahan penting di `CHANGELOG.md`.
- Jika mengubah keputusan arsitektur, buat ADR.
- Jika mengubah requirement, perbarui traceability matrix dan test matrix.

## Pembaruan versi 2

Dokumen tambahan wajib baca:

- `01-domain/PERSON-PATIENT-IDENTITY.md`
- `02-workflows/GATE-USER-SYNC.md`
- `02-workflows/REMOTE-CLINICAL-CONSULTATION.md`
- `05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `07-security/GATE-SYNC-SECURITY.md`
- `07-security/REMOTE-CONSULTATION-GOVERNANCE.md`
- `08-api/GATE-USER-SYNC-CONTRACT.md`
- `12-graphify/GRAPHIFY-INSTALLATION.md`
