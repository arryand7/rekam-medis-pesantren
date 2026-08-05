---
id: DOC-KNOWN-ISSUES
title: "Known Issues and Open Questions"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Known Issues and Open Questions

## Phase 0 Readiness Status

Phase 0 Preflight & Readiness Review telah selesai. Tidak ada *Critical Blocker* yang menghalangi pembuatan fondasi aplikasi Laravel 13.
Isu-isu di bawah ini merupakan poin konfirmasi domain medis & integrasi eksternal yang ditandai sebagai `[PERLU DIKONFIRMASI]` untuk dikerjakan pada fase modul masing-masing.

## Critical (Clinical & Integration Domain — Phase 1 & 2)

- Kewenangan klinis tiap role belum disahkan.
- SOP emergency belum tersedia.
- Kriteria rujukan belum didokumentasikan.
- Belum ada kontrak final field Gate dan stable ID.
- Belum ada SOP red flag untuk consultation vs referral.
- Belum ada daftar mitra dan kanal konsultasi resmi.

## High

- Workflow malam/shift belum jelas.
- Handover belum memiliki format final.
- Informasi yang dibagikan ke wali belum final.
- Kontrak integrasi belum tersedia.
- Kebijakan addendum/approval belum disahkan.
- Definisi akun administratif murni perlu disahkan.
- Strategi legacy identity matching belum disetujui.
- Consent/authority konsultasi eksternal belum ditetapkan.
- Format identitas tenaga kesehatan eksternal belum final.
- Status legal POSKESTREN dalam jejaring konsultasi perlu diverifikasi.

## Medium

- Bed/room management belum jelas.
- Inventory obat belum diketahui tingkat detailnya.
- Retensi dokumen belum final.
- RPO/RTO belum ditentukan.
- Mode offline belum diputuskan.
- Apakah patient profile dibuat eager saat sync atau lazy saat first visit.
- `GEMINI_API_KEY` belum diset di environment CLI untuk otomatisasi Graphify deep semantic extraction.

## Low

- Nama produk final belum dipilih.
- Brand color final belum dipilih.
- Format nomor kunjungan belum dipilih.

Setiap issue harus memiliki owner, target decision, dan hasil keputusan ketika project management aktif.
