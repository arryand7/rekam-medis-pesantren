---
id: ADR-001
title: "Laravel dan Livewire"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# ADR-001 — Laravel dan Livewire

## Status
Accepted.

## Context
Aplikasi dominan berupa form, tabel, workflow, dashboard, dan administrasi. Tim memiliki kompetensi Laravel.

## Decision
Menggunakan Laravel 13, PHP 8.3+, Livewire 4, Blade, Tailwind, dan Flux UI.

## Alternatives
SPA React/Vue penuh, Inertia, atau API-first terpisah.

## Consequences
Deployment dan pengembangan lebih sederhana. Interaksi sangat kompleks harus dievaluasi agar komponen Livewire tetap terjaga.
