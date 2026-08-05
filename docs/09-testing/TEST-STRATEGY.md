---
id: DOC-TEST-STRATEGY
title: "Strategi Pengujian"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Strategi Pengujian

## Level

- Unit: value object, state transition, calculation non-klinis.
- Feature: route/Livewire/API, validation, authorization, transaction.
- Integration: database, queue, storage, external client fake.
- Architecture: dependency dan convention.
- Browser: critical UI dan theme.
- UAT: workflow petugas.

## Prioritas

1. Authorization/IDOR.
2. Lifecycle.
3. Audit.
4. Medication and stock atomicity.
5. Observation/referral guard.
6. Data privacy.
7. Theme and accessibility.

## Data test

Gunakan factory sintetis. Jangan menggunakan data santri nyata.

## CI

- Install locked dependencies.
- Migrate test database.
- Run formatter check.
- Run static analysis.
- Run Pest.
- Build frontend.
