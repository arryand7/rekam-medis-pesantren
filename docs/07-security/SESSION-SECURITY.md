---
id: DOC-SESSION-SECURITY
title: "Keamanan Sesi"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Keamanan Sesi

- Regenerate session setelah login.
- Session timeout disesuaikan risiko dan operasional.
- Lock screen cepat pada workstation bersama.
- Logout semua perangkat.
- Daftar sesi aktif.
- Secure, HttpOnly, SameSite cookie.
- CSRF protection.
- Re-authentication untuk ekspor besar, perubahan akses, atau break-glass.
- Tidak menyimpan token di localStorage bila session cookie cukup.
- Device POSKESTREN menggunakan akun individual, bukan akun bersama.
- Session dan activity sensitif diaudit sesuai kebijakan.
