---
id: DELIVERY-GITHUB-PUBLIC-REPOSITORY-CHECKLIST
title: "GitHub Public Repository Checklist"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# GitHub Public Repository Checklist

## Sebelum membuat remote

- [x] Tree aktif dan seluruh history dipindai untuk secret serta data privat.
- [x] Tidak ada `.env` lokal, key, dump, backup, private export, atau prompt AI tracked.
- [x] `.env.example` berisi placeholder kosong dan endpoint reserved.
- [x] Fixture/seed publik bersifat sintetis dan demo seed opt-in.
- [x] README, SECURITY, threat model, scan evidence, dan release gate tersedia.
- [x] Dependency audit dan quality gate lokal lulus pada entry gate.
- [x] Tidak ada remote, push, atau deployment yang dilakukan agent.
- [ ] Pemilik memilih lisensi atau secara sadar mempertahankan all-rights-reserved/proprietary.
- [ ] Pemilik menetapkan kanal privat pada `SECURITY.md`.
- [ ] Pemilik meninjau Git author metadata dan menerima metadata lokal non-secret di history.
- [ ] Pemilik mengaktifkan branch protection, secret scanning, Dependabot, private vulnerability reporting, dan least-privilege Actions pada GitHub.

## Sebelum setiap push publik

```bash
git status --short
git diff --check
composer validate --strict
composer audit
npm audit
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
php artisan test
npm run build
```

Review staged diff dengan `git diff --cached`, khususnya konfigurasi, dokumentasi, fixture, generated output, dan binary baru. Jangan bypass gate hanya karena secret sudah dihapus dari commit terbaru; scan history harus diulang bila pernah ter-commit.
