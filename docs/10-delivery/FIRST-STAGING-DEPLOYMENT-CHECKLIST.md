---
id: DOC-FIRST-STAGING-DEPLOYMENT-CHECKLIST
title: "First Staging Deployment Checklist"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# First Staging Deployment Checklist

## Sebelum perubahan

- [ ] Approval, operator, commit/tag dan rollback owner tercatat.
- [ ] Server requirement, TLS, proxy CIDR, clock dan disk tervalidasi.
- [ ] Secret staging lengkap; `APP_DEBUG=false`, `SEED_DEMO_DATA=false`.
- [ ] Backup DB/private storage berhasil dan checksum dicatat di sistem bukti, bukan repo.
- [ ] Gate dan Attendance disabled/fake.

## Deployment

- [ ] Install/build hanya dari lockfile.
- [ ] Migration status ditinjau; `migrate --force` sukses.
- [ ] Config/route/view cache sukses.
- [ ] Scheduler tunggal aktif; worker queue sesuai kebutuhan.
- [ ] Ownership dan permission private storage benar.

## Acceptance

- [ ] Health/readiness aman dan 200.
- [ ] Login, logout, rate limit dan akun nonaktif sesuai.
- [ ] Role klinis/farmasi/operasional/manajemen/admin terisolasi.
- [ ] Download dokumen privat memerlukan auth + policy.
- [ ] Audit tercatat tanpa secret/clinical payload mentah.
- [ ] Gate kemudian Attendance divalidasi dengan checklist terpisah.
- [ ] Keputusan GO/ROLLBACK dan waktu server dicatat.
