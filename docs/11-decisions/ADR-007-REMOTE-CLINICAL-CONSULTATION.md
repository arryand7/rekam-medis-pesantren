---
id: ADR-007
title: "Konsultasi Klinis Jarak Jauh"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# ADR-007 — Konsultasi Klinis Jarak Jauh

## Status
Proposed.

## Context

Tim kesehatan membutuhkan ringkasan data agar Puskesmas/rumah sakit dapat memberi pertimbangan tanpa pasien langsung datang.

## Decision

Tambahkan aggregate `ClinicalConsultation` dengan versioned summary, recipient, transmission audit, attributed external advice, dan local clinical decision.

## Constraints

- Tidak menunda emergency referral.
- Tidak menghasilkan diagnosis otomatis.
- Kanal dan mitra harus disetujui.
- External advice terpisah dari local assessment.
- Minimum necessary dan audit.

## Review trigger

Setelah SOP, kerja sama mitra, consent/authority, dan kanal resmi disahkan.
