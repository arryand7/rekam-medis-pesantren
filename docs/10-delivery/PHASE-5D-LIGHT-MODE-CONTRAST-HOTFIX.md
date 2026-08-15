---
id: DOC-PHASE-5D-LIGHT-MODE-CONTRAST-HOTFIX
title: "Phase 5D Light Mode Contrast & Theme Consistency Hotfix"
status: complete
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Phase 5D Light Mode Contrast & Theme Consistency Hotfix

## Masalah

Light mode memakai satu token muted untuk terlalu banyak fungsi, placeholder mengikuti default browser, dan sejumlah view mencampurkan token dengan palet Tailwind langsung. Pada referral, nama class warna status/urgency juga dibentuk secara dinamis sehingga production scanner Tailwind tidak menjamin class tersebut tersedia. Dampaknya adalah teks secondary, chip, hint, filter, dan chart label yang pudar atau tidak konsisten.

## Perubahan

- Memisahkan token `foreground-secondary`, `foreground-muted`, `foreground-tertiary`, `foreground-disabled`, dan `placeholder`.
- Menambahkan `surface-subtle`, `surface-disabled`, dan `border-soft` pada light, dark, serta print mapping.
- Menambahkan shared primitive `ui-card`, `ui-text-*`, `ui-form-*`, `ui-banner-*`, `ui-badge-*`, `ui-filter-chip`, `ui-table-heading`, `ui-chart-label`, dan `ui-choice-*`.
- Memigrasikan daftar/create/detail referral, visit intake, management dashboard, dan separator visit stage ke primitive semantik.
- Mengganti status dan pilihan urgency referral dari class Tailwind dinamis menjadi mapping/selector semantik deterministik.

Tidak ada perubahan schema, route/API, authorization, validation, atau domain logic.

## Halaman dan mode yang diverifikasi

| Target | Light | Dark | System | Catatan |
|---|---|---|---|---|
| `/referrals` dan pola create/detail | Inspeksi token + kontrak view | Token dark dipertahankan | Mewarisi token aktif | Badge, urgency, table heading, destination, action metadata |
| `/visits/create` | Inspeksi token + kontrak view | Token dark dipertahankan | Mewarisi token aktif | Label, placeholder, hint, warning override, disabled state global |
| `/dashboards/management` | Inspeksi token + kontrak view | Token dark dipertahankan | Mewarisi token aktif | Filter, period ribbon, KPI labels, chart legend/axis, pharmacy state |

In-app browser automation tidak tersedia pada sesi ini. Karena itu tidak ada klaim screenshot/manual visual pass; verifikasi pengganti dilakukan melalui audit source desktop-responsive markup, test rasio kontras token, test kontrak view, targeted feature render tests, dan production asset build. Browser smoke light/dark/system tetap menjadi checklist pada staging pertama.

## Verifikasi otomatis

- Pasangan teks light/dark yang diperkenalkan diuji terhadap target WCAG AA 4.5:1 untuk normal text.
- Test melarang kembalinya class warna dinamis pada referral index/create dan memastikan halaman prioritas memakai primitive bersama.
- Targeted regression suite: 26 tests / 164 assertions, PASS.
- `npm run build`: PASS.
- Full suite: 279 tests / 1.268 assertions, PASS.
- Pint: PASS; PHPStan 512 MB: PASS (0 errors); `git diff --check`: PASS.

## Risiko tersisa

- Dukungan `color-mix()` mengikuti browser modern yang menjadi target Vite/Laravel saat ini; warna teks/background tetap mempunyai fallback token independen.
- Visual smoke aktual perlu diulang pada desktop light/dark/system ketika browser staging tersedia.
