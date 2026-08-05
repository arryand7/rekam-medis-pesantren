---
id: DOC-GATE-SYNC-SECURITY
title: "Keamanan Sinkronisasi Gate"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Keamanan Sinkronisasi Gate

## Authentication

- Gunakan service credential khusus aplikasi.
- Scope read-only untuk user detail bila cukup.
- Secret tidak tampil di UI/log.
- Rotasi credential.

## Data integrity

- TLS.
- Signature atau token audience yang benar.
- Source timestamp/version.
- Checksum payload.
- Pagination cursor.
- Idempotency.
- Reject schema yang tidak dikenal.

## Authorization

- Hanya super admin atau service job yang dapat apply.
- Operator dapat melihat dry-run sesuai permission.
- Patient health data tidak pernah dikirim balik saat identity sync.

## Conflict handling

- Tidak auto-merge berdasarkan nama.
- Collision identifier masuk manual review.
- Deactivation tidak hard delete.
- Field lokal tidak menimpa authoritative field.

## Audit

Catat actor, source, cursor, totals, conflict, before/after, dan correlation ID. Hindari menyimpan secret atau payload sensitif yang tidak diperlukan.
