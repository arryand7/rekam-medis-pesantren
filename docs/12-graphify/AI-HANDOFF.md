---
id: DOC-AI-HANDOFF
title: "AI Handoff"
status: draft
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# AI Handoff

## Instruksi awal

1. Baca `AGENTS.md`.
2. Baca `PROJECT-STATUS.md`.
3. Jangan mengubah kode sebelum readiness review.
4. Cari konflik, gap, dan asumsi.
5. Tulis hasil pada `docs/10-delivery/READINESS-REVIEW.md`.
6. Perbarui `plans/KNOWN-ISSUES.md`.
7. Perbarui `PROJECT-STATUS.md`.

## Prompt first run

```text
Baca AGENTS.md dan semua dokumen wajib.

Jangan mengubah kode.

Lakukan repository readiness review:
- konsistensi domain,
- gap business rules,
- workflow yang belum selesai,
- data model,
- access control,
- security,
- theme,
- test strategy,
- deployment,
- Graphify traceability.

Tulis laporan di docs/10-delivery/READINESS-REVIEW.md.
Beri severity Critical/High/Medium/Low.
Berikan rekomendasi urutan penyelesaian.
Perbarui PROJECT-STATUS.md dan plans/KNOWN-ISSUES.md.
```

## Handoff setelah task

Catat:
- tujuan,
- requirement IDs,
- file berubah,
- migration,
- test,
- risiko,
- keputusan,
- next step,
- commit.

## Tambahan review wajib

AI harus memeriksa:

- pemisahan Person/User/Patient;
- definisi human vs administrative account;
- Gate source-of-truth fields;
- idempotency dan reconciliation;
- clinical consultation state machine;
- emergency guard;
- attribution external advice;
- secure document handling;
- Graphify installation dan output.
