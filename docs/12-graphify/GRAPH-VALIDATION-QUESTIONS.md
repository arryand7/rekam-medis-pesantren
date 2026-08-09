---
id: DOC-GRAPH-QUESTIONS
title: "Pertanyaan Validasi Graph"
status: draft
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# Pertanyaan Validasi Graph

## Coverage

- Requirement mana yang belum memiliki implementation target?
- Business rule mana yang belum memiliki test?
- Screen mana yang tidak memiliki authorization mapping?
- Entity mana yang tidak ada di data dictionary?
- Endpoint mana yang tidak memiliki Policy?
- Event medis mana yang tidak menghasilkan audit?
- Migration mana yang tidak tercermin pada ERD?
- Module mana yang memiliki dependency silang tidak sah?

## Security

- Jalur apa yang memungkinkan wali kelas membaca diagnosis?
- Apakah actor dapat berasal dari request payload?
- Apakah timestamp klinis dapat dimanipulasi client?
- Di mana file medis dibuat public?
- Apakah ada hard delete pada medical aggregate?

## Operations

- Apa dampak jika Redis mati?
- Job integrasi mana yang tidak idempotent?
- Queue mana yang menyimpan data sensitif?

## Identity and Gate

- Apakah ada code path yang mengubah field Gate melalui form lokal?
- Apakah role admin digunakan untuk menentukan patient eligibility?
- Apakah user deactivation menyebabkan cascade delete?
- Apakah legacy matching berdasarkan nama dapat auto-merge?
- Apakah semua sync action memiliki audit dan idempotency test?

## Consultation

- Apakah red flag dapat tertahan oleh status consultation?
- Apakah external advice memiliki attribution?
- Apakah summary lama tetap ada setelah revisi?
- Apakah document path public?
- Apakah recipient dapat diganti setelah final tanpa audit?
