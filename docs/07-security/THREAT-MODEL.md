---
id: DOC-THREAT-MODEL
title: "Threat Model"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Threat Model

## Aset

- Rekam medis.
- Identitas santri.
- Credential.
- Dokumen rujukan.
- Stok obat.
- Audit log.
- Integrasi.

## Ancaman utama

1. IDOR antar-santri.
2. Privilege escalation.
3. Akun bersama.
4. Session dicuri pada perangkat POSKESTREN.
5. Data medis muncul di log.
6. Upload berbahaya.
7. Manipulasi timestamp/status dari client.
8. Race condition stok.
9. Penghapusan jejak.
10. Integrasi palsu/replay.
11. Ekspor massal.
12. Insider misuse.
13. Backup bocor.
14. Device hilang.
15. Dependency rentan.

## Kontrol

Policy, MFA bila tersedia, session timeout, server timestamp, transaction/lock, private storage, audit, rate limit, signed integration, idempotency, backup encryption, review akses.
