---
id: DOC-SECURITY-REQ
title: "Security Requirements"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Security Requirements

- Authentication wajib pada seluruh area internal.
- Authorization per resource dan action.
- Least privilege.
- Session regeneration setelah login.
- CSRF untuk web.
- Rate limiting.
- Secure cookie, HttpOnly, SameSite.
- TLS.
- Input validation.
- Output escaping.
- File validation dan private storage.
- MIME/type/size/checksum untuk upload.
- Query parameter tidak boleh membocorkan PHI.
- Sensitive response memakai cache-control yang tepat.
- Secret hanya dari environment/secret manager.
- Error production tidak menampilkan stack trace.
- Audit akses dan mutasi sensitif.
- Security headers.
- Dependency update berkala.
- Backup terenkripsi.
- Test IDOR dan privilege escalation.
