---
id: ADR-002
title: "Modular Monolith"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# ADR-002 — Modular Monolith

## Status
Accepted.

## Decision
Satu aplikasi dan database utama dengan boundary domain yang eksplisit.

## Rationale
Skala awal belum membutuhkan microservices. Konsistensi transaksi medis dan operasional deployment lebih penting.

## Consequences
Boundary harus dijaga melalui folder, contract, action, event, test, dan architecture rule. Modul tidak boleh menjadi kumpulan model yang saling mengakses tanpa batas.
