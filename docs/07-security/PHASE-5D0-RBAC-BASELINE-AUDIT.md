---
id: DOC-PHASE-5D0-RBAC-AUDIT
title: "Phase 5D0 RBAC Baseline Audit"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D0 RBAC Baseline Audit

RBAC memakai model `User`, `Role`, `Permission` dan pivot ULID. Role memberikan permission default; direct user permission hanya exception terkontrol. `Gate::before` tidak memberi akses berdasarkan label admin semata, melainkan effective permission/super-admin yang terdefinisi.

Temuan acceptance dan resolusi:

- Pivot direct permission belum tersedia: ditambah migration additive `model_has_permissions`.
- UI administrasi hanya list: dilengkapi create/show/edit/delete role dan detail/assignment user.
- Admin biasa berpotensi mengubah role `admin`: role `admin` dan `super_admin` kini protected.
- Seeder `sync()` dapat menghapus grant lokal: diganti `syncWithoutDetaching()`.
- Perlindungan last super-admin: diterapkan pada pencabutan role dan deaktivasi, dengan row locking untuk toggle status.
- Semua perubahan role, direct permission dan status akun menghasilkan audit trail.

Status baseline: **PASS untuk local pre-staging**, dengan Gate entitlement tetap orthogonal terhadap local RBAC.
