---
id: DOC-GRAPHIFY-GUIDE
title: "Panduan Penggunaan Graphify"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# Panduan Penggunaan Graphify

Baca `GRAPHIFY-INSTALLATION.md` untuk instalasi.

## Tujuan graph

Menghubungkan:
- business rule;
- workflow;
- requirement;
- person/user/patient;
- Gate sync;
- visit;
- clinical consultation;
- Policy;
- table;
- event;
- audit;
- test.

## Build

Gunakan skill dari dalam assistant:

```text
Codex:  $graphify . --mode deep
Lainnya: /graphify . --mode deep
```

Jangan menggunakan `--code-only`, karena Markdown menyimpan sumber kebenaran domain.

## Update

```text
$graphify . --update
```

atau:

```text
/graphify . --update
```

## Query validasi prioritas

```bash
graphify query "requirements without tests"
graphify query "how Gate identity reaches patient records"
graphify query "what prevents direct edit of Gate authoritative fields"
graphify query "how emergency referral bypasses remote consultation"
graphify query "which policies protect external clinical advice"
graphify path "GateClient" "Patient"
graphify path "ClinicalConsultation" "Referral"
```

## Review output

- `graph.html`: inspeksi visual.
- `GRAPH_REPORT.md`: architecture report dan pertanyaan.
- `graph.json`: query CLI/MCP.

Setelah perubahan besar, update graph dan catat hasil pada `PROJECT-STATUS.md`.
