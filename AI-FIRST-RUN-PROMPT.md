---
id: DOC-AI-FIRST-RUN
title: "Prompt Pertama untuk AI"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Prompt Pertama untuk AI

```text
Anda bekerja pada proyek SABIRA POSKESTREN Health.

Baca AGENTS.md dan seluruh dokumen yang diwajibkan di dalamnya.
Jangan mengubah kode atau membuat migration terlebih dahulu.

Lakukan repository readiness review menyeluruh terhadap:
1. konsistensi konteks domain,
2. konflik dan gap business rules,
3. kelengkapan workflow,
4. kelengkapan requirement dan acceptance criteria,
5. data model dan state machine,
6. access control dan privacy,
7. arsitektur Laravel,
8. UI/UX light-dark-system,
9. testing,
10. deployment dan operasi,
11. traceability Graphify.

Tulis temuan ke docs/10-delivery/READINESS-REVIEW.md.
Klasifikasikan severity sebagai Critical, High, Medium, atau Low.
Jangan mengarang keputusan medis. Tandai hal yang harus dikonfirmasi stakeholder.
Perbarui plans/KNOWN-ISSUES.md dan PROJECT-STATUS.md.
Akhiri dengan rekomendasi urutan kerja berikutnya.
```

## Tambahan fokus

Pastikan readiness review juga memeriksa:
- desain Person/User/Patient,
- Gate field ownership,
- sinkronisasi idempotent dan conflict handling,
- eligibility pasien untuk pengguna manusia yang memiliki role admin,
- konsultasi klinis jarak jauh,
- emergency referral guard,
- data minimization dan attribution,
- instalasi Graphify project-scoped.
