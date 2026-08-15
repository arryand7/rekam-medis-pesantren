---
id: DOC-RBAC-PRIVILEGE-ESCALATION
title: "RBAC Privilege Escalation Protection"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# RBAC Privilege Escalation Protection

- Role `super_admin` dan `admin` adalah protected dan tidak dapat diubah/dihapus oleh admin terdelegasi.
- Admin terdelegasi tidak dapat menambah atau mencabut protected role pada user.
- Protected permission tidak dapat ditambahkan ke custom role atau direct grant oleh non-super-admin.
- User non-super tidak dapat mengubah direct permission dirinya sendiri.
- Super-admin aktif terakhir tidak dapat dinonaktifkan atau kehilangan role.
- Role yang masih dipakai user aktif tidak dapat dihapus.
- Semua mutation divalidasi, diotorisasi server-side, transactional dan diaudit.
- Seeder tidak mengurangi assignment permission lokal yang sudah ada.

Test negatif utama berada di `tests/Feature/Security/RbacPrivilegeEscalationTest.php`; UI hiding bukan kontrol keamanan.
