---
id: ADR-003
title: "Integrasi Gate SSO"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# ADR-003 — Integrasi Gate SSO

## Status
Proposed.

## Decision
Gate direncanakan sebagai identity provider dan pengatur akses aplikasi. Development boleh memakai local auth terisolasi.

## Constraints
- Mapping external ID stabil.
- Deactivation disinkronkan.
- Permission klinis tetap dikontrol aplikasi.
- Kegagalan Gate tidak boleh merusak rekam medis yang sudah tersimpan.

## Review trigger
Setelah kontrak OAuth/OIDC dan API akses final tersedia.
