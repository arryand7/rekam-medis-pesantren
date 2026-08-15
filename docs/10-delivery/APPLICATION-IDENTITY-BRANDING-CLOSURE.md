---
id: DELIVERY-APPLICATION-IDENTITY-BRANDING-CLOSURE
title: "Application Identity & Branding Closure"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Application Identity & Branding Closure

## Delivery

- Version target: `0.25.0`.
- Persistence: additive single-row `application_identities`; source fallback bekerja tanpa row.
- Read model: `ApplicationIdentityService` dengan cache dan immediate invalidation setelah update/reset.
- UI: Super Admin settings, preview light/dark, secure raster upload, reset confirmation, dan global shell/login integration.
- Default assets: empat SVG project-owned di `public/branding/default/`.
- PWA/manifest: `NOT-APPLICABLE` karena project tidak memiliki manifest.
- Deployment: tidak termasuk scope dan tidak dilakukan.

## Verification record

- Full suite: 305 tests / 1.429 assertions, PASS.
- Pint: PASS; PHPStan: PASS (0 error); Vite production build: PASS.
- Composer audit: tidak ada security advisory; npm audit: 0 vulnerability.
- Graphify `graphify update .`: PASS, menghasilkan 3.628 nodes / 5.817 edges; query pasca-update menemukan model, service, controller, docs, dan test branding baru.
- Public repository scan: tidak ada tracked prompt, `.env`, private key, dump, backup, atau runtime branding. Transient prompt dipindahkan secara recoverable ke `/private/tmp` dan tidak di-commit.
- Default SVG dirender ke preview raster lokal dan diperiksa: light/dark wordmark serta favicon terbaca dan tidak memakai protected red humanitarian emblem.
- In-app Browser/Node REPL tidak tersedia pada sesi ini. Automated render/security/theme contract lulus, tetapi matrix viewport serta interaction Light/Dark/System tetap `MANUAL VISUAL CHECK PENDING`; bukti browser tidak difabrikasi.
- Final commit SHA dan clean working tree direkam pada laporan akhir setelah commit dibuat.

## Remaining owner choice

- `[PERLU DIKONFIRMASI]` actual institution logo yang boleh dipublikasikan/diunggah; repository hanya membawa default generic yang aman untuk publik.

## Classification

`APPLICATION-IDENTITY-BRANDING-COMPLETE-WITH-MANUAL-VISUAL-CHECK`

NO STAGING DEPLOYMENT WAS PERFORMED.

NO PRODUCTION DEPLOYMENT WAS PERFORMED.
