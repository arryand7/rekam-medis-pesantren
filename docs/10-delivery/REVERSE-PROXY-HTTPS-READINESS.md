---
id: DOC-REVERSE-PROXY-HTTPS-READINESS
title: "Reverse Proxy and HTTPS Readiness"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Reverse Proxy and HTTPS Readiness

Laravel default `TrustProxies` membaca `config/trustedproxy.php`. `TRUSTED_PROXIES` menerima daftar IP/CIDR terkontrol dan kosong secara default. Wildcard dilarang kecuali seluruh jalur jaringan secara independen dipercaya.

Checklist staging:

- [ ] TLS certificate valid dan auto-renew terpantau.
- [ ] HTTP dialihkan ke HTTPS tanpa loop.
- [ ] `APP_URL` dan Gate callback menggunakan HTTPS hostname staging.
- [ ] `SESSION_SECURE_COOKIE=true`, HttpOnly true, SameSite sesuai flow SSO.
- [ ] `X-Forwarded-Proto/Host/Port/For` hanya dipercaya dari proxy yang terdaftar.
- [ ] Direct backend port tidak dapat diakses publik.
- [ ] Upload limit, timeout dan security headers disepakati.
- [ ] Redirect login/callback, asset URL dan generated URL diuji dari browser eksternal.

Hostname, proxy IP/CIDR dan certificate chain tetap **[PERLU DIKONFIRMASI]** di staging.
