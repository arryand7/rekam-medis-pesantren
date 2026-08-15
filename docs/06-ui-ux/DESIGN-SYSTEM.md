---
id: DOC-DESIGN-SYSTEM
title: "Design System"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Design System

## Semantic tokens

- `background`
- `surface`
- `surface-muted`
- `surface-subtle`
- `surface-disabled`
- `foreground`
- `foreground-secondary`
- `foreground-muted`
- `foreground-tertiary`
- `foreground-disabled`
- `placeholder`
- `border`
- `border-soft`
- `primary`
- `action-bg`, `action-hover`, `action-text`, dan `link`
- `success`
- `warning`
- `danger`
- `info`
- `focus-ring`

Token teks harus dipilih berdasarkan fungsi, bukan warna palet: `foreground` untuk isi utama, `foreground-secondary` untuk label, `foreground-muted` untuk keterangan, dan `foreground-tertiary` hanya untuk metadata non-kritis. Placeholder dan state disabled tetap wajib terbaca.

## Primitive reusable

- `ui-card` dan `ui-surface-subtle` untuk permukaan dan batas section.
- `ui-text-secondary`, `ui-text-muted`, dan `ui-text-tertiary` untuk hierarki teks.
- `ui-form-label`, `ui-form-hint`, dan `ui-form-control` untuk form, placeholder, serta state disabled.
- `ui-banner-{info|warning|success|danger}` untuk panel informasi.
- `ui-badge-{info|warning|success|danger|neutral}` untuk status/chip.
- `ui-chart-label`, `ui-table-heading`, dan `ui-filter-chip` untuk visualisasi, tabel, dan toolbar.

Primitive tersebut mengambil warna dari CSS variable light/dark. View tidak boleh membentuk nama class Tailwind warna secara dinamis.

## Komponen inti

- App shell.
- Sidebar dan mobile navigation.
- Page header.
- Stat card.
- Status badge.
- Alert medis.
- Patient identity banner.
- Timeline.
- Form section.
- Data table.
- Empty state.
- Confirmation dialog.
- Audit drawer.
- Theme switcher.

## Status

Status harus memiliki label teks, ikon, dan warna. Jangan menggunakan warna sebagai satu-satunya pembeda.

## Spacing

Gunakan skala Tailwind konsisten. Form klinis memberi ruang cukup untuk pemindaian visual cepat.
