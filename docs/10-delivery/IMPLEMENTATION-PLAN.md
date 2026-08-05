---
id: DOC-IMPLEMENTATION-PLAN
title: "Rencana Implementasi"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Rencana Implementasi

## Fase 0

- Install Laravel dan Livewire starter.
- Configure MariaDB.
- Install Pest, Pint, Larastan, Boost.
- Setup CI.
- Implement app shell dan theme.
- Add health check.
- Baseline test.
- Readiness review.

## Fase 1

- User, role, permission.
- Policy baseline.
- Session security.
- Audit event/service.
- Local dev auth dan contract SSO.
- Tests IDOR.

## Fase 2

- Student sync model.
- Health profile.
- Allergy and condition.
- Data conflict report.
- Tests.

## Fase 3

- Medical visit aggregate.
- State machine.
- Registration.
- Vital signs.
- Assessment.
- Disposition.
- Workspace UI.
- Tests and audit.

## Fase 4–8

Ikuti roadmap. Setiap fase wajib memiliki:
- migration plan,
- rollback plan,
- acceptance criteria,
- test,
- docs update,
- Graphify update,
- status report.

## Checkpoint AI

AI tidak boleh melompat fase tanpa memperbarui `PROJECT-STATUS.md`.

## Fase 0A — Graphify installation

- Install `graphifyy`.
- Register project-scoped skill untuk Codex/Gemini/assistant yang digunakan.
- Build graph code + Markdown.
- Review `GRAPH_REPORT.md`.
- Query identity, consultation, access control, dan traceability.
- Tentukan apakah `graphify-out/` dikomit.

## Fase 1A — Gate and patient identity

- Finalize Gate contract.
- Implement `Person`, `User`, `Patient`.
- Dry-run sync.
- Apply sync.
- Reconciliation.
- Eligibility tests.
- Legacy mapping.

## Fase 6A — Remote consultation

- Partner/facility master.
- Consultation aggregate and state machine.
- Summary composer.
- Versioned private document.
- Secure transmission adapter.
- External advice.
- Emergency guard.
- Audit and UAT.
