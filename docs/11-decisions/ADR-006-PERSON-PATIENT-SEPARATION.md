---
id: ADR-006
title: "Pemisahan Person, User, Role, dan Patient"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# ADR-006 — Pemisahan Person, User, Role, dan Patient

## Status
Accepted.

## Context

Semua tipe pengguna manusia selain akun administratif/teknis murni dapat menjadi pasien. Pengguna juga dapat memiliki role admin, sehingga role tidak cocok dijadikan discriminator pasien.

## Decision

Pisahkan:
- `Person`: manusia dan proyeksi identitas Gate.
- `User`: akun login.
- `Role/Permission`: kewenangan aplikasi.
- `Patient`: subjek rekam kesehatan.

## Consequences

- Riwayat bertahan saat role/status berubah.
- Guru/staf/admin manusia dapat menjadi pasien.
- Dibutuhkan mapping dan migration legacy yang hati-hati.
- Query harus menggunakan patient ID untuk rekam medis.
