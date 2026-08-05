---
id: ADR-004
title: "Medical Audit Log"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# ADR-004 — Medical Audit Log

## Status
Accepted.

## Decision
Audit medis append-only dan terpisah dari application log biasa.

## Data
Actor, action, subject, before/after, reason, request context, correlation ID, timestamp.

## Consequences
Storage bertambah dan perlu kebijakan retensi. Audit writing harus efisien dan konsisten dengan transaksi.
