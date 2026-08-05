---
id: DOC-DATA-FLOW
title: "Aliran Data"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Aliran Data

## Registrasi kunjungan

```mermaid
sequenceDiagram
    actor P as Petugas
    participant UI
    participant A as RegisterMedicalVisitAction
    participant DB
    participant EV as Event/Audit
    P->>UI: Isi keluhan
    UI->>A: Validated command
    A->>DB: Lock/check active visit
    A->>DB: Insert visit
    A->>EV: MedicalVisitRegistered
    EV->>DB: Append audit
    A-->>UI: Visit created
```

## Prinsip

- Actor berasal dari authentication context.
- Timestamp resmi dibuat server.
- Validasi client hanya bantuan UX.
- Side effect notifikasi berjalan setelah commit.
- Audit untuk transaksi kritis harus konsisten dengan hasil transaksi.
