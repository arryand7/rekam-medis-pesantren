---
id: DOC-GATE-STAGING-CHECKLIST
title: "Gate Staging Configuration Checklist"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Gate Staging Configuration Checklist

- [ ] Provider base URL, authorization/token/userinfo endpoints dan TLS chain disetujui.
- [ ] Client ID/secret disimpan melalui UI Super Admin; verifikasi ciphertext database dan callback HTTPS persis sama dengan nilai pada halaman Pengaturan Gate SSO.
- [ ] Scope minimum, application code, entitlement status dan expiry/refresh semantics dikonfirmasi.
- [ ] `[PERLU DIKONFIRMASI]` kontrak issuer/audience/JWKS atau jaminan provider untuk validasi token; aplikasi saat ini memperoleh identity dari TLS token endpoint + userinfo, bukan validasi signature ID token lokal.
- [ ] State/nonce, replay, expired code, provider timeout dan error redaction diuji.
- [ ] User allowed/revoked/not-assigned/nonactive menghasilkan hasil yang benar.
- [ ] Sinkronisasi preview sebelum apply; apply tetap idempoten, transactional, diaudit dan tidak overwrite konflik.
- [ ] Deaktivasi Gate menonaktifkan akun tanpa menghapus person, patient atau riwayat medis.
- [ ] Rekonsiliasi konflik mempunyai reviewer dan jejak audit.
- [ ] Rate limit callback dan sync apply diverifikasi.

Aktifkan SSO melalui UI Super Admin hanya setelah callback/provider lulus. `GATE_SYNC_APPLY_ENABLED` dan webhook tetap diaktifkan terpisah setelah setiap tahap lulus. Verifikasi lokal tidak melakukan panggilan Gate nyata.
