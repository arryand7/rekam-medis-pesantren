---
id: DOC-PHASE-5D0-RBAC-ADMIN-UX
title: "Phase 5D0 RBAC Administration UX"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D0 RBAC Administration UX

Administrasi sistem menyediakan daftar/detail user, assignment role, direct permission exception, status akun, serta daftar/create/detail/edit/delete role. UI hanya convenience layer; setiap mutation tetap divalidasi oleh Form Request dan diotorisasi server-side.

- Permission dikelompokkan per domain agar matriks dapat ditinjau.
- Effective permission menampilkan sumber role versus direct grant.
- Role inti `super_admin` dan `admin` ditandai protected.
- Admin terdelegasi tidak dapat mengubah protected roles/permissions atau direct permission dirinya sendiri.
- Role yang digunakan user aktif tidak dapat dihapus; super-admin terakhir tidak dapat dinonaktifkan.
- Sidebar dan tombol mengikuti permission, tetapi direct URL tetap bergantung pada Gate/Request authorization.

Light/dark/responsive tetap harus direvalidasi pada browser staging pertama; test render/menu otomatis lulus lokal.
