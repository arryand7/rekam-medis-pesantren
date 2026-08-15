---
id: DOC-PRE-STAGING-SECURITY-CHECKLIST
title: "Pre-Staging Security Checklist"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Pre-Staging Security Checklist

- [x] Server-side policy/gate melindungi capability sensitif.
- [x] Mutation memakai non-GET methods, validation dan CSRF web middleware.
- [x] Protected role/permission tidak dapat diberikan atau dicabut oleh admin terdelegasi.
- [x] Super-admin aktif terakhir tidak dapat dinonaktifkan/dicabut.
- [x] File klinis berada di private storage dan di-stream oleh controller berizin.
- [x] Audit payload menyensor auth secret; upstream body/exception mentah tidak dipersist.
- [x] APP_DEBUG=false response tidak memuat stack trace, SQL atau path lokal.
- [x] Login, SSO callback, Gate apply dan report export mempunyai rate limit proporsional.
- [x] Seeder demo opt-in dan tidak menghapus grant RBAC lokal.
- [ ] Secret staging berada di secret manager dan sudah dirotasi sebelum deployment pertama.
- [ ] TLS/proxy header trust tervalidasi dari jaringan staging.
- [ ] Gate token/provider contract serta Attendance channel disetujui.
- [ ] Backup encryption, retention dan restore drill staging lulus.

Checklist bertanda kosong adalah manual/staging value, bukan kegagalan local code acceptance.
