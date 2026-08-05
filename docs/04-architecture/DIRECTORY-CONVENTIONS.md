---
id: DOC-DIRECTORY-CONVENTIONS
title: "Konvensi Direktori"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Konvensi Direktori

```text
app/
├── Actions/
│   ├── Visits/
│   ├── Observations/
│   ├── Medication/
│   └── Referrals/
├── Data/
├── Domain/
│   ├── Visits/
│   ├── Observations/
│   └── Shared/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
├── Integrations/
├── Livewire/
├── Models/
├── Notifications/
├── Policies/
├── Queries/
├── Services/
└── Support/
```

## Penamaan

- Action: kata kerja, contoh `RegisterMedicalVisitAction`.
- Query: tujuan baca, contoh `ActiveObservationQuery`.
- Event: kejadian lampau, contoh `MedicalVisitRegistered`.
- Listener: efek, contoh `WriteMedicalAuditLog`.
- Policy: resource, contoh `MedicalVisitPolicy`.
- Test: perilaku, contoh `RegisterMedicalVisitTest`.

## Rule

Jangan membuat folder generik `Helpers` untuk logic domain.
