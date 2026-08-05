---
id: DOC-API-AUTH
title: "Autentikasi API"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Autentikasi API

## Web internal
Session cookie + CSRF.

## First-party API
Sanctum atau mekanisme resmi yang disetujui.

## Service-to-service
Client credential/mTLS/signed request sesuai kemampuan sistem integrasi.

## Prinsip

- Token memiliki scope.
- Token dapat dicabut.
- Jangan gunakan token pengguna untuk job service.
- Secret diputar.
- Request integration memiliki timestamp/nonce bila signed.
- Endpoint sensitif memiliki rate limit.
- Semua authorization tetap dilakukan setelah authentication.
