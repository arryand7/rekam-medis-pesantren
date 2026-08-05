---
id: DOC-APP-LAYERS
title: "Lapisan Aplikasi"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Lapisan Aplikasi

## Presentation

- Routes.
- Controllers.
- Livewire components.
- Blade/Flux views.
- Form Request.
- API Resource.

Tanggung jawab: input/output, bukan business logic.

## Application

- Action classes.
- Use case orchestration.
- Transaction boundary.
- Authorization coordination.
- Command/query DTO.

## Domain

- Entities/model behavior.
- Enum.
- Value object.
- Domain rule.
- State transition.
- Domain event.

## Infrastructure

- Eloquent persistence.
- External API clients.
- File storage.
- Queue.
- Notification channels.
- Audit persistence.

## Contoh alur

`Livewire Component -> Form validation -> Action -> Policy -> Transaction -> Domain event -> Listener/Queue -> Audit/Notification`
